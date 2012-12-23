<?php

namespace Siciarek\PhotoGalleryBundle\Controller;

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

    public function preExecute()
    {
        $this->config = $this->container->getParameter("siciarek_photo_gallery.config");
        $this->doctrine = $this->getDoctrine();
        $this->em = $this->doctrine->getEntityManager();

        $this->frames = array(
            "ok"      => array(
                "success"   => true,
                "type"      => "info",
                "datetime"  => date("Y-m-d H:i:s"),
                "msg"       => "OK",
                "data"      => new \stdClass(),
            ),
            "data"    => array(
                "success"   => true,
                "type"      => "data",
                "datetime"  => date("Y-m-d H:i:s"),
                "msg"       => "Data",
                "totalCount"=> 0,
                "data"      => array(),
            ),
            "error"   => array(
                "success"   => false,
                "type"      => "error",
                "datetime"  => date("Y-m-d H:i:s"),
                "msg"       => "Error",
                "totalCount"=> 0,
                "data"      => new \stdClass(),
            ),
            "warning" => array(
                "success"   => true,
                "type"      => "warning",
                "datetime"  => date("Y-m-d H:i:s"),
                "msg"       => "Warning",
                "data"      => new \stdClass(),
            ),
        );
    }

    /**
     * @Route("/{id}/image/{slug}.{format}", name = "_photogallery_api_show_image", requirements = {"id"="^[1-9]\d*$", "format"="^(jpe?g|gif|png|svg)$", "slug"="^[a-z0-9][a-z0-9\-]*[a-z0-9]$"}))
     * @Template()
     */
    public function showImageAction($id, $slug, $format)
    {

        $image = $this->em->getRepository("SiciarekPhotoGalleryBundle:Image")->find($id);

        $file = $this->config["uploads_directory"] . $image->getPath();
        $mime_type = $image->getMimeType();

        return $this->fileResponse($file, $mime_type);
    }

    /**
     * @Route("/{id}/thumbnail.{format}", name = "_photogallery_api_show_thumbnail", requirements = {"id"="^[1-9]\d*$", "format"="^(jpe?g|gif|png|svg)$"}))
     * @Template()
     */
    public function showThumbnailAction($id, $format)
    {
        $config = $this->container->getParameter("siciarek_photo_gallery.config");
        $this->doctrine = $this->getDoctrine();
        $this->em = $this->doctrine->getEntityManager();
        $image = $this->em->getRepository("SiciarekPhotoGalleryBundle:Image")->find($id);
        $thumbnail = $image->getThumbnail();

        $file = $config["uploads_directory"] . $thumbnail->getPath();
        $mime_type = $thumbnail->getMimeType();

        return $this->fileResponse($file, $mime_type);
    }

    /**
     * @Route("/create-new-album.json", name = "_photogallery_api_create_new_album")
     * @Template()
     */
    public function createNewAlbumAction(Request $request)
    {
        $config = $this->container->getParameter("siciarek_photo_gallery.config");
        $this->doctrine = $this->getDoctrine();
        $this->em = $this->doctrine->getEntityManager();

        $frame = array(
            "success"   => true,
            "type"      => "data",
            "datetime"  => date("Y-m-d H:i:s"),
            "msg"       => "Data",
            "totalCount"=> 0,
            "data"      => array(),
        );

        $title = $request->get("title");
        $description = $request->get("description");
        $description = trim($description);
        $title = trim($title);
        $description = empty($description) ? null : $description;
        $title = empty($title) ? null : $title;
        $is_visible = $request->get("hidden", "off") !== "on";

        $qb = $this->em->createQueryBuilder();
        $qb->select("max(a.sequence_number)")->from("SiciarekPhotoGalleryBundle:Album", "a");
        $query = $qb->getQuery();
        $sequence_number = $query->getSingleScalarResult();

        $album = new Album();
        $album->setTitle($title);
        $album->setDescription($description);
        $album->setIsVisible($is_visible);
        $album->setSequenceNumber($sequence_number + 1);
        $this->em->persist($album);
        $this->em->flush();

        $files = Request::createFromGlobals()->files->get("photos");
        $this->createImages($files, $album, $title, $description, $is_visible);

        $frame["data"]["album"] = $album->getId();

        $json = json_encode($frame);

        return $this->jsonResponse($json);
    }

    /**
     * @Route("/{album}/{photos}/delete-photos.json", name = "_photogallery_api_delete_photos", requirements = {"album"="^[1-9]\d*$", "photos"="^\s*\d+\s*(,\s*(\d+)?)*\s*$"})
     * @Template()
     */
    public function deletePhotosAction($album, $photos)
    {
        $frame = array();

        try {
            $ids = explode(",", $photos);
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

            $frame = $this->frames["ok"];
            $frame["data"] = $ids;
        } catch (\Exception $e) {
            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        $json = json_encode($frame);

        return $this->jsonResponse($json);
    }

    /**
     * @Route("/{album}/{photos}/reorder-photos.json", name = "_photogallery_api_reorder_photos", requirements = {"album"="^[1-9]\d*$", "photos"="^\s*\d+\s*(,\s*(\d+)?)*\s*$"})
     */
    public function reorderImagesAction($album, $photos)
    {
        $frame = array();

        try {
            $ids = explode(",", $photos);
            $ids = array_unique($ids);
            $ids = array_map("intval", $ids);

            $ids = array_filter($ids, function ($item) {
                return $item > 0;
            });

            $ids = array_map("intval", $ids);

            $alb = $this->em->getRepository("SiciarekPhotoGalleryBundle:Album")->find($album);

            $qb = $this->em->createQueryBuilder();
            $qb->select("i", "a")
                ->from("SiciarekPhotoGalleryBundle:Image", "i")
                ->leftJoin("i.albums", "a")
                ->andWhere("a.id = :aid")->setParameter("aid", $alb->getId())
                ->andWhere("i.id in (:ids)")->setParameter("ids", $ids);

            $query = $qb->getQuery();
            $images = $query->getResult();
            $imcount = count($images);

            $order = array();

            foreach ($ids as $id) {
                $order[$id] = $imcount--;
            }

            foreach($images as $image) {
                $image->setSequenceNumber($order[$image->getId()]);
                $this->em->persist($image);
            }

            $this->em->flush();

            $frame = $this->frames["ok"];
            $frame["msg"] = "Album has been updated successfully";
            $frame["data"] = $ids;
        } catch (\Exception $e) {
            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        $json = json_encode($frame);

        return $this->jsonResponse($json);
    }

    /**
     * @Route("/{album}/{image}/delete-photos.json", name = "_photogallery_api_change_cover", requirements = {"album"="^[1-9]\d*$", "image"="^[1-9]\d*$"})
     */
    public function changeCoverAction($album, $image)
    {
        $frame = array();

        try {
            $album = $this->em->getRepository("SiciarekPhotoGalleryBundle:Album")->find($album);
            $cover = $this->em->getRepository("SiciarekPhotoGalleryBundle:Image")->find($image);
            $album->setCover($cover);

            $this->em->persist($album);
            $this->em->flush();

            $frame = $this->frames["ok"];
            $frame["msg"] = "Album cover has been changed successfully";
        } catch (\Exception $e) {
            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        $json = json_encode($frame);

        return $this->jsonResponse($json);
    }

    /**
     * @Route("/add-photos.json", name = "_photogallery_api_add_new_photos")
     * @Template()
     */
    public function addNewPhotosAction(Request $request)
    {

        $this->doctrine = $this->getDoctrine();
        $this->em = $this->doctrine->getEntityManager();

        $frame = array(
            "success"   => true,
            "type"      => "data",
            "datetime"  => date("Y-m-d H:i:s"),
            "msg"       => "Data",
            "totalCount"=> 0,
            "data"      => array(),
        );

        $title = $request->get("title");
        $description = $request->get("description");
        $description = trim($description);
        $title = trim($title);
        $description = empty($description) ? null : $description;
        $title = empty($title) ? null : $title;
        $is_visible = $request->get("hidden", "off") !== "on";
        $album_id = intval($request->get("album", 0));

        $qb = $this->em->createQueryBuilder();
        $qb->select("max(a.sequence_number) as c")->from("SiciarekPhotoGalleryBundle:Image", "a");
        $query = $qb->getQuery();
        $sequence_number = $query->getSingleScalarResult();

        $album = $this->em->getRepository("SiciarekPhotoGalleryBundle:Album")->find($album_id);

        $files = Request::createFromGlobals()->files->get("photos");

        $this->createImages($files, $album, $title, $description, $is_visible, $sequence_number);

        $frame["data"]["album"] = $album->getId();

        $json = json_encode($frame);

        return $this->jsonResponse($json);
    }

    /**
     * @Route("/album-list.json", name = "_photogallery_api_album_list")
     * @Template()
     */
    public function albumListAction()
    {
        $config = $this->container->getParameter("siciarek_photo_gallery.config");
        $this->request = Request::createFromGlobals();
        $this->doctrine = $this->getDoctrine();
        $this->em = $this->doctrine->getEntityManager();

        sleep(3);

        $qb = $this->em->createQueryBuilder();

        $qb->select("a", "c", "i")
            ->from("SiciarekPhotoGalleryBundle:Album", "a")
            ->leftJoin("a.images", "i")
            ->leftJoin("a.cover", "c")
            ->addOrderBy("a.sequence_number", "DESC")
            ->addOrderBy("a.id", "DESC");
        ;

        $query = $qb->getQuery();
        $data = $query->getArrayResult();

        $json = json_encode($data);

        return $this->jsonResponse($json);
    }

    /**
     * @Route("/{id}/album.json", name = "_photogallery_api_album", requirements = {"id"="^[1-9]\d*$"})
     * @Template()
     */
    public function albumAction($id)
    {
        $config = $this->container->getParameter("siciarek_photo_gallery.config");

        try {
            $album = $this->em->getRepository("SiciarekPhotoGalleryBundle:Album")->find($id);

            if ($album === null) {
                throw new \Exception("Requested album is not available.");
            }

            $images = array();

            if ($album->getImages()->count() > 0) {
                $qb = $this->em->createQueryBuilder();
                $qb->select("a", "i", "t")
                    ->from("SiciarekPhotoGalleryBundle:Album", "a")
                    ->leftJoin("a.images", "i")
                    ->innerJoin("i.thumbnail", "t")
                    ->andWhere("a.id = :aid")->setParameter("aid", $id)
                    ->orderBy("i.sequence_number", "DESC");
                ;
                $query = $qb->getQuery();
                $data = $query->getArrayResult();
                $images = $data[0]["images"];
            }

            $frame = $this->frames["data"];
            $frame["msg"] = sprintf("%s;;;%s", $album->getTitle(), $album->getDescription());
            $frame["data"] = $images;
            $frame["totalCount"] = count($frame["data"]);
        } catch (\Exception $e) {
            $frame = $this->frames["error"];
            $frame["msg"] = $e->getMessage();
            $frame["data"] = $e->getTraceAsString();
        }

        $json = json_encode($frame);

        return $this->jsonResponse($json);
    }

    protected function setAlbumCover($album)
    {

        $this->em->refresh($album);

        foreach ($album->getImages() as $im) {
            if ($im->getThumbnail() !== null) {
                $album->setCover($im);
                break;
            }
        }

        $this->em->persist($album);
        $this->em->flush();
    }

    protected function createImages($files, $album, $title, $description, $is_visible, $seq_number = 0)
    {
        $config = $this->container->getParameter("siciarek_photo_gallery.config");
        $sequence_number = $seq_number;

        if ($files === null) {
            return;
        }

        foreach ($files as $file) {
            $source_path = $file->getPathName();

            // Initial values:
            $image_file_size = 0;
            $image_width = 0;
            $image_height = 0;
            $image_mime_type = "application/octet-stream";
            $image_extension = "jpg";
            $image_path = "/no/path/is/set";
            $thumbnail_path = "/no/path/is/set";

            $thumbnail_file_size = 0;
            $thumbnail_width = 200;
            $thumbnail_height = 150;
            $thumbnail_mime_type = "image/png";
            $thumbnail_extension = "png";

            $image = new Image();
            $image->setTitle($title);
            $image->setDescription($description);
            $image->setIsVisible($is_visible);
            $image->setSequenceNumber(++$sequence_number);

            $image->setWidth($image_width);
            $image->setHeight($image_height);
            $image->setMimeType($image_mime_type);
            $image->setFileSize($image_file_size);
            $image->addAlbum($album);
            $image->setPath($image_path);

            $this->em->persist($image);
            $this->em->flush();
            $this->em->refresh($image);

            $thumbnail = new Image();
            $thumbnail->setIsVisible($image->getIsVisible());
            $thumbnail->setSequenceNumber(0);
            $thumbnail->setWidth($thumbnail_width);
            $thumbnail->setHeight($thumbnail_height);
            $thumbnail->setMimeType($thumbnail_mime_type);
            $thumbnail->setFileSize($thumbnail_file_size);
            $thumbnail->setPath($thumbnail_path);
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

            $image->setWidth($image_width);
            $image->setHeight($image_height);
            $image->setMimeType($image_mime_type);
            $image->setFileSize($image_file_size);
            $image->setPath($impath);

            // Thumbnail:

            $thmb_size = new \Imagine\Image\Box($thumbnail_width, $thumbnail_height);

            $th = $im->thumbnail($thmb_size)->save($thumbnail_path);
            $thumbnail_file_size = filesize($thumbnail_path);
            $thumbnail_width = $th->getSize()->getWidth();
            $thumbnail_height = $th->getSize()->getHeight();


            $thumbnail->setWidth($thumbnail_width);
            $thumbnail->setHeight($thumbnail_height);
            $thumbnail->setMimeType($thumbnail_mime_type);
            $thumbnail->setFileSize($thumbnail_file_size);
            $thumbnail->setPath($thumpath);

            $this->em->persist($thumbnail);
            $this->em->persist($image);
            $this->em->flush();
        }

        if ($album->getCover() === null) {
            $this->setAlbumCover($album);
        }
    }

    protected function fileResponse($path, $mime_type)
    {
        $response = new Response();
        $response->headers->set('Content-Type', $mime_type);
        $response->headers->set('Content-Length', filesize($path));

        readfile($path);

        return $response;
    }

    protected function jsonResponse($json)
    {
        $response = new Response();
        $response->headers->set('Content-Type', "application/json");
        $response->headers->set('Content-Length', strlen($json));

        // If you are using a https connection, you have to set those two headers for compatibility with IE <9
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        $response->setContent($json);

        return $response;
    }
}
