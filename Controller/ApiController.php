<?php

namespace Siciarek\PhotoGalleryBundle\Controller;

ini_set("memory_limit", "256M");
ini_set('gd.jpeg_ignore_warning', 1);

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Doctrine\Common\Collections;

use \Siciarek\PhotoGalleryBundle\Entity as E;
use \Siciarek\PhotoGalleryBundle\Entity\Album;
use \Siciarek\PhotoGalleryBundle\Entity\Image;

/**
 * API controller.
 *
 * @Route("/api")
 */
class ApiController extends Controller
{
    protected $config, $doctrine, $em;
    protected $frames = array();

    /* PUBLIC ACTIONS: */

    /**
     * @Route("/album-list.json", name = "_photogallery_api_album_list")
     */
    public function albumListAction()
    {
        $frame = array();

        try {
            $qb = $this->em->createQueryBuilder();

            $qb->select("a", "i", "c", "t", "cr")
                ->from("SiciarekPhotoGalleryBundle:Album", "a")
                ->leftJoin("a.creator", "cr")
                ->leftJoin("a.images", "i")
                ->leftJoin("a.cover", "c")
                ->leftJoin("c.thumbnail", "t")
                ->addOrderBy("a.sequence_number", "DESC")
                ->addOrderBy("a.id", "DESC");
            ;

            if ($this->getUser() === null) {
                $qb->andWhere("a.is_visible = 1");
            } else {
                $qb->andWhere("cr.username = :usrname")->setParameter("usrname", $this->getUser()->getUsername());
            }

            $query = $qb->getQuery();
            $data = $query->getArrayResult();

            $frame = $this->frames["data"];

            $frame["msg"] = "Album list";
            $frame["data"] = $data;
            $frame["totalCount"] = count($data);
        } catch (\Exception $e) {
            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        return $this->jsonResponse($frame);
    }

    /**
     * @Route("/{id}/album.json", name = "_photogallery_api_album", requirements = {"id"="^[1-9]\d*$"})
     */
    public function albumAction($id)
    {
        try {

            $params = array("id" => $id);

            if ($this->getUser() === null) {
                $params["is_visible"] = true;
            }

            $album = $this->em->getRepository("SiciarekPhotoGalleryBundle:Album")->findOneBy($params);

            if ($album === null) {
                throw new \Exception("Requested album is not available.");
            }

            if($this->getUser() !== null and $album->getCreator()->getUsername() !== $this->getUser()->getUsername()) {
                throw new \Exception("Album is not available in edit mode.");
            }

            $images = array();

            if ($album->getImages()->count() > 0) {
                $qb = $this->em->createQueryBuilder();
                $qb->select("a", "i", "t", "if", "tf", "cr")
                    ->from("SiciarekPhotoGalleryBundle:Album", "a")
                    ->leftJoin("a.images", "i")
                    ->leftJoin("a.creator", "cr")
                    ->leftJoin("i.file", "if")
                    ->leftJoin("i.thumbnail", "t")
                    ->leftJoin("t.file", "tf")
                    ->andWhere("a.id = :aid")->setParameter("aid", $id)
                    ->orderBy("i.sequence_number", "DESC");
                ;

                if ($this->getUser() === null) {
                    $qb->andWhere("i.is_visible = 1");
                } else {
                    $qb->andWhere("cr.username = :usrname")->setParameter("usrname", $this->getUser()->getUsername());
                }


                $query = $qb->getQuery();
                $data = $query->getArrayResult();
                $images = $data[0]["images"];
            }

            $cover_id = $album->getCover() === null ? 0 : $album->getCover()->getId();
            $frame = $this->frames["data"];
            $frame["msg"] = sprintf("%d;;;%s;;;%s;;;%d;;;%d", $album->getId(), $album->getTitle(), $album->getDescription(), $album->getIsVisible(), $cover_id);
            $frame["data"] = $images;
            $frame["totalCount"] = count($frame["data"]);
        } catch (\Exception $e) {
            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        return $this->jsonResponse($frame);
    }

    /**
     * @Route("/{id}/{type}.{format}", name = "_photogallery_api_show_image",      defaults={"type"="image"},     requirements = {"id"="^[1-9]\d*$", "format"="^(jpe?g|gif|png|svg)$"}))
     * @Route("/{id}/{type}.{format}", name = "_photogallery_api_show_thumbnail",  defaults={"type"="thumbnail"}, requirements = {"id"="^[1-9]\d*$", "format"="^(jpe?g|gif|png|svg)$"}))
     * @Route("/{id}/{type}.{format}", name = "_photogallery_api_show_original",   defaults={"type"="original"},  requirements = {"id"="^[1-9]\d*$", "format"="^(jpe?g|gif|png|svg)$"}))
     */
    public function showImageAction($id, $type, $format)
    {
        $params = array("id" => $id);

        if ($this->getUser() === null) {
            $params["is_visible"] = true;
        }

        $image = $this->em->getRepository("SiciarekPhotoGalleryBundle:Image")->findOneBy($params);

        if ($image !== null and $type === "thumbnail") {
            $image = $image->getThumbnail();
        }


        return $this->fileResponse($image, $type);
    }

    /* PRIVATE ACTIONS: */

    /**
     * @Route("/create-new-album.json", name = "_photogallery_api_create_new_album")
     */
    public function createNewAlbumAction(Request $request)
    {
        $frame = array();

        try {
            $this->em->getConnection()->beginTransaction();
            $this->checkAccess();

            $id = intval($request->get("id"));
            $title = $request->get("title");
            $description = $request->get("description");

            $title = trim($title);
            $description = trim($description);
            $title = empty($title) ? "New Album" : $title;
            $description = empty($description) ? null : $description;

            $images_visible = $request->get("hidden", "off") !== "on";
            $is_visible = $request->get("publish", "off") === "on";

            $qb = $this->em->createQueryBuilder();
            $qb->select("max(a.sequence_number)")->from("SiciarekPhotoGalleryBundle:Album", "a");
            $query = $qb->getQuery();
            $sequence_number = $query->getSingleScalarResult();

            $album = new Album();
            $album->setSequenceNumber($sequence_number + 1);

            if ($id > 0) {
                $album = $this->em->getRepository("SiciarekPhotoGalleryBundle:Album")->find($id);
            }

            $creator = $this->getCreator();
            $album->setCreator($creator);

            $album->setTitle($title);
            $album->setDescription($description);
            $album->setIsVisible($is_visible);

            $files = Request::createFromGlobals()->files->get("photos");
            $this->createImages($files, $album, $images_visible);

            $frame = $this->frames["info"];


            $this->em->persist($album);
            $this->em->flush();

            $frame["data"] = array(
                "type" => "album",
                "id"   => $album->getId(),
                "slug" => $album->getSlug(),
            );

            $frame["msg"] = sprintf("Album has been %s successfully", $id > 0 ? "updated" : "created");

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();

            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        return $this->jsonResponse($frame);
    }

    /**
     * @Route("/add-images.json", name = "_photogallery_api_add_new_images")
     */
    public function addNewImagesAction(Request $request)
    {
        $frame = array();

        try {
            $this->em->getConnection()->beginTransaction();
            $this->checkAccess();

            $title = $request->get("title");
            $description = $request->get("description");
            $description = trim($description);
            $title = trim($title);
            $description = empty($description) ? null : $description;
            $title = empty($title) ? null : $title;

            $album_id = intval($request->get("album", 0));

            $is_visible = $request->get("publish", "off") === "on";

            $imginfojson = $request->get("imginfo", "{}");
            $imginfo = json_decode($imginfojson, true);

            $image_id = intval($request->get("id", 0));
            $album = $this->em->getRepository("SiciarekPhotoGalleryBundle:Album")->find($album_id);


            $frame = $this->frames["info"];

            if ($image_id > 0) {
                $image = $this->em->getRepository("SiciarekPhotoGalleryBundle:Image")->find($image_id);
                $image->setTitle($title);
                $image->setDescription($description);
                $image->setIsVisible($is_visible);

                if ($album_id !== $image->getAlbum()->getId()) {
                    $image->setAlbum($album);
                }

                $this->em->persist($image);
                $this->em->flush();

                $frame["msg"] = "Image has been updated successfully";
            } else {

                $qb = $this->em->createQueryBuilder();
                $qb->select("max(i.sequence_number) + 1 as c")
                    ->from("SiciarekPhotoGalleryBundle:Image", "i")
                    ->leftJoin("i.album", "a")
                    ->andWhere("i.album = :al")->setParameter("al", $album);
                $query = $qb->getQuery();
                $sequence_number = $query->getSingleScalarResult();

                $files = Request::createFromGlobals()->files->get("photos");

                $this->createImages($files, $album, true, $sequence_number, $imginfo);

                $frame["msg"] = "Images has been added successfully";
            }

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();

            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        return $this->jsonResponse($frame);
    }

    /**
     * @Route("/{id}/delete-{element}.json", name = "_photogallery_api_delete_element", requirements = {"id"="^[1-9]\d*$", "element"="^(album|image)$"})
     */
    public function deleteElementAction($id, $element)
    {
        $frame = array();

        try {
            $this->checkAccess();

            $elemname = ucfirst($element);

            $obj = $this->em->getRepository("SiciarekPhotoGalleryBundle:" . $elemname)->find($id);

            if ($element === "image") {
                $album = $obj->getAlbum();

                if ($album->getCover() !== null and $album->getCover()->getId() === $obj->getId()) {
                    $album->setCover(null);

                    $this->em->persist($album);
                    $this->em->flush();
                }
            }

            $this->em->remove($obj);
            $this->em->flush();

            $frame = $this->frames["info"];
            $frame["msg"] = $elemname . " has been deleted successfuly";
        } catch (\Exception $e) {
            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        return $this->jsonResponse($frame);
    }

    /**
     * @Route("/{image}/{album}/{action}.json", name = "_photogallery_api_copy_image", requirements = {"image"="^[1-9]\d*$", "album"="^[1-9]\d*$", "action"="^(copy|move)\-to$"})
     */
    public function copyImageAction($image, $album, $action)
    {
        $frame = array();

        try {
            $this->checkAccess();

            $move = $action === "move-to";
            $im = $this->em->getRepository("SiciarekPhotoGalleryBundle:Image")->find($image);
            $album = $this->em->getRepository("SiciarekPhotoGalleryBundle:Album")->find($album);
            $copy = null;

            if ($move === true) {
                // Change album:

                $imalbum = $im->getAlbum();

                if($imalbum->getCover()->getId() === $im->getId()) {
                    $imalbum->setCover(null);
                }

                $im->setAlbum($album);
                $this->em->persist($imalbum);
                $this->em->persist($im);


            } else {
                $qb = $this->em->createQueryBuilder();
                $qb->select("max(i.sequence_number) + 1 as c")
                    ->from("SiciarekPhotoGalleryBundle:Image", "i")
                    ->leftJoin("i.album", "a")
                    ->andWhere("a.id = :aid")->setParameter("aid", $album->getId());
                $query = $qb->getQuery();
                $sequence_number = intval($query->getSingleScalarResult());

                $copy = new E\Image();
                $copy->setIsVisible($im->getIsVisible());
                $copy->setTitle($im->getTitle());
                $copy->setDescription($im->getDescription());
                $copy->setAlbum($album);
                $copy->setFile($im->getFile());
                $copy->setThumbnail($im->getThumbnail());
                $copy->setSequenceNumber($sequence_number);

                $this->em->persist($copy);
            }

            $this->em->flush();
            $frame = $this->frames["info"];
            $frame["msg"] = sprintf("Image has been %s successfuly", $move === true ? "moved" : "copied");
            $frame["data"] = array($image, $action, $album);
        } catch (\Exception $e) {
            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        return $this->jsonResponse($frame);
    }

    /**
     * @Route("/{id}/{action}-{element}.json", name = "_photogallery_api_show_hide_element", requirements = {"id"="^[1-9]\d*$", "action"="^(hide|show)$", "element"="^(album|image)$"})
     */
    public function showHideElementAction($id, $action, $element)
    {
        $frame = array();

        try {
            $this->checkAccess();

            $visible = $action === "show";

            $elemname = ucfirst($element);

            $obj = $this->em->getRepository("SiciarekPhotoGalleryBundle:" . $elemname)->find($id);
            $obj->setIsVisible($visible);

            if ($element === "image") {
                $album = $obj->getAlbum();

                if ($album->getCover() !== null and $album->getCover()->getId() === $obj->getId()) {
                    $album->setCover(null);
                    $this->em->persist($album);
                }
            }

            $this->em->persist($obj);
            $this->em->flush();

            $frame = $this->frames["info"];
            $frame["msg"] = sprintf($elemname . " is now %s", $action === "show" ? "visible" : "hidden");
        } catch (\Exception $e) {
            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        return $this->jsonResponse($frame);
    }

    /**
     * @Route("/{album}/{images}/delete-images.json", name = "_photogallery_api_delete_images", requirements = {"album"="^[1-9]\d*$", "images"="^\s*\d+\s*(,\s*(\d+)?)*\s*$"})
     */
    public function deleteImagesAction($album, $images)
    {
        $frame = array();

        try {
            $this->checkAccess();

            $ids = explode(",", $images);
            $ids = array_unique($ids);
            $ids = array_map("intval", $ids);

            $ids = array_filter($ids, function ($item) {
                return $item > 0;
            });

            $ids = array_map("intval", $ids);
            sort($ids);

            $album = $this->em->getRepository("SiciarekPhotoGalleryBundle:Album")->find($album);

            $qb = $this->em->createQueryBuilder();
            $qb->select("i")
                ->from("SiciarekPhotoGalleryBundle:Image", "i")
                ->andWhere("i.id in (:ids)")->setParameter("ids", $ids);

            $query = $qb->getQuery();
            $images = $query->getResult();

            foreach ($images as $image) {
                $image->removeAlbum($album);
            }

            if ($album->getImages()->count() === 0) {
                $album->setCover(null);
            }

            $this->em->persist($album);
            $this->em->flush();

            $frame = $this->frames["info"];
            $frame["data"] = $ids;
        } catch (\Exception $e) {
            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        return $this->jsonResponse($frame);
    }

    /**
     * @Route("/{elements}/reorder-{collection}.json", name = "_photogallery_api_reorder_sequence", requirements = {"elements"="^\s*\d+\s*(,\s*(\d+)?)*\s*$", "collection"="^(albums|images)$"})
     */
    public function reorderAlbumsAction($elements, $collection)
    {
        $frame = array();

        try {
            $this->checkAccess();

            $collection = ucfirst($collection);
            $class = preg_replace('/s$/', "", $collection);

            $ids = explode(",", $elements);
            $ids = array_unique($ids);
            $ids = array_map("intval", $ids);

            $ids = array_filter($ids, function ($item) {
                return $item > 0;
            });

            $ids = array_map("intval", $ids);

            $qb = $this->em->createQueryBuilder();
            $qb->select("o")
                ->from("SiciarekPhotoGalleryBundle:" . $class, "o")
                ->andWhere("o.id in (:ids)")->setParameter("ids", $ids);

            $query = $qb->getQuery();
            $objs = $query->getResult();
            $objcount = count($objs);

            $order = array();

            foreach ($ids as $id) {
                $order[$id] = $objcount--;
            }

            foreach ($objs as $obj) {
                $obj->setSequenceNumber($order[$obj->getId()]);
                $this->em->persist($obj);
            }

            $this->em->flush();

            $frame = $this->frames["info"];
            $frame["msg"] = $collection . " sequence has been updated successfully";
            $frame["data"] = $ids;
        } catch (\Exception $e) {
            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        return $this->jsonResponse($frame);
    }

    /**
     * @Route("/{album}/{image}/change-album-cover.json", name = "_photogallery_api_change_album_cover", requirements = {"album"="^[1-9]\d*$", "image"="^[1-9]\d*$"})
     */
    public function changeCoverAction($album, $image)
    {
        $frame = array();

        try {
            $this->checkAccess();

            $album = $this->em->getRepository("SiciarekPhotoGalleryBundle:Album")->find($album);
            $cover = $this->em->getRepository("SiciarekPhotoGalleryBundle:Image")->find($image);
            $album->setCover($cover);

            $this->em->persist($album);
            $this->em->persist($cover);
            $this->em->flush();

            $frame = $this->frames["info"];
            $frame["msg"] = "Album cover has been changed successfully";
        } catch (\Exception $e) {
            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        return $this->jsonResponse($frame);
    }


    /* HELPER METHODS: */

    protected function getCreator($create = true)
    {
        $user = $this->getUser();
        $username = $user->getUsernameCanonical();
        $email = $user->getEmailCanonical();

        $params = array(
            "username"   => $username,
            "email"      => $email,
        );

        $creator = $this->em->getRepository("SiciarekPhotoGalleryBundle:Creator")->findOneBy($params);

        if ($create === true and $creator === null) {
            $creator = new E\Creator();
            $creator->setUsername($username);
            $creator->setEmail($email);

            $this->em->persist($creator);
            $this->em->flush();
            $this->em->refresh($creator);
        }

        return $creator;
    }

    protected function checkAccess()
    {
        $exception = new \Exception("Access denied.");

        if ($this->getUser() === null and $this->getCreator(false) !== null) {
            throw $exception;
        }

        $user = $this->getUser();
        $user->setLoggedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();
    }

    protected function setAlbumCover($album)
    {

        $this->em->refresh($album);

        foreach ($album->getImages() as $im) {
            if ($im->getThumbnail() !== null) {
                $album->setCover($im);
            }
        }

        $this->em->persist($album);
        $this->em->flush();
    }

    protected function createImages($files, $album, $images_visible = true, $seq_number = 0, $fileinfo = array())
    {
        $config = $this->container->getParameter("siciarek_photo_gallery.config");
        $sequence_number = $seq_number;

        if ($files === null) {
            return;
        }

        $index = 0;

        foreach ($files as $file) {

            $original_name = $file->getClientOriginalName();

            $fkey = ($index++) . $original_name;

            $original_name = trim($original_name);
            $original_name = preg_replace("|([^/]+)$|", "$1", $original_name);
            $original_name = preg_replace("/\.\w+$/", "", $original_name);
            $original_name = preg_replace("/_/", " ", $original_name);
            $original_name = preg_replace("/\s+/", " ", $original_name);
            $original_name = trim($original_name);
            $original_name = empty($original_name) ? null : $original_name;

            $info = (is_array($fileinfo) and array_key_exists($fkey, $fileinfo)) ? $fileinfo[$fkey] : array();

            $title = array_key_exists("title", $info) ? $info["title"] : $original_name;
            $description = array_key_exists("description", $info) ? $info["description"] : null;
            $is_visible = array_key_exists("is_visible", $info) ? $info["is_visible"] : $images_visible;
            $album_id = array_key_exists("album_id", $info) ? $info["album_id"] : $album->getId();

            $source_path = $file->getPathName();

            // Initial values:
            $image_extension = "jpg";

            $thumbnail_width = 200;
            $thumbnail_height = 150;
            $thumbnail_mime_type = "image/png";
            $thumbnail_extension = "png";

            $image = new Image();
            $image->setTitle($title);
            $image->setDescription($description);
            $image->setIsVisible($is_visible);
            $image->setSequenceNumber(++$sequence_number);
            $image->setAlbum($album);

            $this->em->persist($image);
            $this->em->flush();
            $this->em->refresh($image);

            $thumbnail = new Image();
            $thumbnail->setIsVisible($image->getIsVisible());
            $thumbnail->setSequenceNumber(0);


            $this->em->persist($thumbnail);

            $image->setThumbnail($thumbnail);
            $this->em->persist($image);
            $this->em->flush();

            $origdir = sprintf("/%02d/originals", $album->getId());
            $imdir = sprintf("/%02d/images", $album->getId());
            $thdir = sprintf("/%02d/thumbnails", $album->getId());

            if (!file_exists($config["uploads_directory"] . $origdir)) {
                mkdir($config["uploads_directory"] . $origdir, 0777, true);
            }

            if (!file_exists($config["uploads_directory"] . $imdir)) {
                mkdir($config["uploads_directory"] . $imdir, 0777, true);
            }

            if (!file_exists($config["uploads_directory"] . $thdir)) {
                mkdir($config["uploads_directory"] . $thdir, 0777, true);
            }

            $origpath = sprintf("%s/%02d.%s", $origdir, $image->getId(), $image_extension);
            $impath = sprintf("%s/%02d.%s", $imdir, $image->getId(), $image_extension);
            $thumpath = sprintf("%s/%02d.%s", $thdir, $image->getId(), $thumbnail_extension);

            $original_path = $config["uploads_directory"] . $origpath;
            $image_path = $config["uploads_directory"] . $impath;
            $thumbnail_path = $config["uploads_directory"] . $thumpath;


            // Image file adjustment:

            $img_width = 640;
            $img_height = 480;
            $wide_img_width = 1000;

            $imagine = new \Imagine\Gd\Imagine();
            $im = $imagine->open($source_path)->save($original_path);

            $img_size = new \Imagine\Image\Box($img_width, $img_height);
            $wide_img_size = new \Imagine\Image\Box($wide_img_width, $img_height);

            $im = $im->thumbnail($img_size)->save($image_path);

            if ($im->getSize()->getHeight() < 200) {
                $im = $imagine->open($source_path)->save($original_path);
                $im = $im->thumbnail($wide_img_size)->save($image_path);
            }

            $image_file_size = filesize($image_path);
            $image_width = $im->getSize()->getWidth();
            $image_height = $im->getSize()->getHeight();
            $image_mime_type = $file->getClientMimeType();

            $imfile = new E\File();
            $imfile->setWidth($image_width);
            $imfile->setHeight($image_height);
            $imfile->setMimeType($image_mime_type);
            $imfile->setFileSize($image_file_size);
            $imfile->setPath($impath);

            $this->em->persist($imfile);
            $this->em->flush();
            $this->em->refresh($imfile);
            $image->setFile($imfile);

            // Thumbnail:

            $thmb_size = new \Imagine\Image\Box($thumbnail_width, $thumbnail_height);

            $th = $im->thumbnail($thmb_size)->save($thumbnail_path);
            $thumbnail_file_size = filesize($thumbnail_path);
            $thumbnail_width = $th->getSize()->getWidth();
            $thumbnail_height = $th->getSize()->getHeight();


            $thfile = new E\File();
            $thfile->setWidth($thumbnail_width);
            $thfile->setHeight($thumbnail_height);
            $thfile->setMimeType($thumbnail_mime_type);
            $thfile->setFileSize($thumbnail_file_size);
            $thfile->setPath($thumpath);

            $this->em->persist($thfile);
            $this->em->flush();
            $this->em->refresh($thfile);
            $thumbnail->setFile($thfile);

            $this->em->persist($thumbnail);
            $this->em->persist($image);
            $this->em->flush();


            // Add watermark:

            $this->addWatermark($original_path);
            $this->addWatermark($image_path);

            $imfile->setFileSize(filesize($image_path));
            $this->em->persist($image);
            $this->em->flush();
        }

        if ($album->getCover() === null) {
            $this->setAlbumCover($album);
        }
    }

    protected function addWatermark($image_path, $alpha = 10)
    {
        $watermark_path = $this->config["watermark"];

        if (file_exists($watermark_path) and is_readable($watermark_path)) {

            $watermark = imagecreatefrompng($watermark_path);
            $watermark_width = (int)imagesx($watermark);
            $watermark_height = (int)imagesy($watermark);

            $src_image = imagecreatefromjpeg($image_path);
            $src_image_width = (int)imagesx($src_image);
            $src_image_height = (int)imagesy($src_image);
            imagealphablending($src_image, true);

            $dest_x = intval(($src_image_width - $watermark_width) / 2);
            $dest_y = intval(($src_image_height - $watermark_height) / 2);

            imagecopymerge($src_image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $alpha);
            imagejpeg($src_image, $image_path, 100);
            imagedestroy($watermark);
            imagedestroy($src_image);
        }
    }

    protected function fileResponse($image, $type = null)
    {
        if ($image !== null) {
            $file = $image->getFile();
            $path = $this->config["uploads_directory"] . $file->getPath();
            $content_type = $file->getMimeType();
            $content_length = $file->getFileSize();

            if ($type === "original") {
                $path = preg_replace('|/images/|', '/originals/', $path);
            }
        } else {
            $path = $this->config["image_not_found"];
            $content_type = "image/png";
            $content_length = filesize($path);
        }

        if (!file_exists($path) or !is_readable($path)) {
            $path = $this->config["image_not_found"];
            $content_type = "image/png";
            $content_length = filesize($path);
        } else {
            if ($type === "original") {
                $content_length = filesize($path);
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', $content_type);
        $response->headers->set('Content-Length', $content_length);

        readfile($path);

        return $response;
    }

    protected function jsonResponse($frame)
    {
        $json = json_encode($frame);

        $response = new Response();
        $response->headers->set('Content-Type', "application/json");
        $response->headers->set('Content-Length', strlen($json));

        // If you are using a https connection, you have to set those two headers for compatibility with IE <9
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        $response->setContent($json);

        return $response;
    }

    public function preExecute()
    {
        $this->config = $this->container->getParameter("siciarek_photo_gallery.config");
        $this->doctrine = $this->getDoctrine();
        $this->em = $this->doctrine->getEntityManager();

        $this->frames = array(
            "info"      => array(
                "success"   => true,
                "type"      => "info",
                "datetime"  => date("Y-m-d H:i:s"),
                "msg"       => "OK",
                "data"      => new \stdClass(),
            ),
            "data"      => array(
                "success"   => true,
                "type"      => "data",
                "datetime"  => date("Y-m-d H:i:s"),
                "msg"       => "Data",
                "totalCount"=> 0,
                "data"      => array(),
            ),
            "error"     => array(
                "success"   => false,
                "type"      => "error",
                "datetime"  => date("Y-m-d H:i:s"),
                "msg"       => "Error",
                "data"      => new \stdClass(),
            ),
            "warning"   => array(
                "success"   => true,
                "type"      => "warning",
                "datetime"  => date("Y-m-d H:i:s"),
                "msg"       => "Warning",
                "data"      => new \stdClass(),
            ),
        );
    }
}
