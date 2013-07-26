<?php

namespace Siciarek\PhotoGalleryBundle\Controller;

ini_set("memory_limit", "256M");
ini_set('gd.jpeg_ignore_warning', 1);

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
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
 * @Route("/api/public")
 * @Cache(maxage="10")
 */
class ApiPublicController extends Controller
{
    protected $config, $doctrine, $em;
    protected $frames = array();
    protected $errorMessages = array();

    /* PUBLIC ACTIONS: */

    /**
     * @Route("/album-list.json", name = "_photogallery_api_public_album_list")
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
            $frame = $this->handleException($e);
        }

        return $this->jsonResponse($frame);
    }

    /**
     * @Route("/{id}/album.json", name = "_photogallery_api_public_album", requirements = {"id"="^[1-9]\d*$"})
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
            $frame = $this->handleException($e);
        }

        return $this->jsonResponse($frame);
    }

    /**
     * @Route("/{id}/{type}.{format}", name = "_photogallery_api_public_show_image",      defaults={"type"="image"},     requirements = {"id"="^[1-9]\d*$", "format"="^(jpe?g|gif|png|svg)$"}))
     * @Route("/{id}/{type}.{format}", name = "_photogallery_api_public_show_thumbnail",  defaults={"type"="thumbnail"}, requirements = {"id"="^[1-9]\d*$", "format"="^(jpe?g|gif|png|svg)$"}))
     * @Route("/{id}/{type}.{format}", name = "_photogallery_api_public_show_original",   defaults={"type"="original"},  requirements = {"id"="^[1-9]\d*$", "format"="^(jpe?g|gif|png|svg)$"}))
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

    protected function handleException(\Exception $e, $rollback = false) {
        $msg = $e->getMessage();
        $data = $e->getTrace();

        $env = $this->get('kernel')->getEnvironment();

        if($env === "prod") {
            $msg = "Unexpected Exception.";
            $data = new \stdClass();
        }

        if($rollback === true) {
            $this->em->getConnection()->rollback();
        }

        $frame = $this->frames["error"];
        $frame["msg"] = $msg;
        $frame["data"] = $data;

        return $frame;
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


    protected function jsonResponse($data)
    {
        $json = json_encode($data);

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
        $this->watermark = $this->get("image.filter.watermark");
        $this->doctrine = $this->getDoctrine();
        $this->em = $this->doctrine->getManager();

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
