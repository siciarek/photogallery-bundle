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

use \Siciarek\PhotoGalleryBundle\Entity as E;

/**
 * API controller.
 *
 * @Route("/photogallery")
 */
class ApiController extends Controller
{
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
        $is_visible = $request->get("hidden", "off") !== "on";

        $qb = $this->em->createQueryBuilder();
        $qb->select("max(a.sequence_number) as c")
            ->from("SiciarekPhotoGalleryBundle:Album", "a")
        ;

        $query = $qb->getQuery();
        $sequence_number = $query->getSingleScalarResult();

        $album = new E\Album();
        $album->setTitle($title);
        $album->setDescription($description);
        $album->setIsVisible($is_visible);
        $album->setSequenceNumber($sequence_number + 1);
        $this->em->persist($album);
        $this->em->flush();


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

        $qb = $this->em->createQueryBuilder();

//        $qb->select("a", "c", "i")
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

        $frame = array(
            "success"   => true,
            "type"      => "data",
            "datetime"  => date("Y-m-d H:i:s"),
            "msg"       => "Data",
            "totalCount"=> 0,
            "data"      => array(),
        );

        $config = $this->container->getParameter("siciarek_photo_gallery.config");
        $this->request = Request::createFromGlobals();
        $this->doctrine = $this->getDoctrine();
        $this->em = $this->doctrine->getEntityManager();

        $album = $this->em->getRepository("SiciarekPhotoGalleryBundle:Album")->find($id);

        $images = array();

        if($album->getImages()->count() > 0) {
            $qb = $this->em->createQueryBuilder();
            $qb->select("a", "i", "t")
                ->from("SiciarekPhotoGalleryBundle:Album", "a")
                ->leftJoin("a.images", "i")
                ->innerJoin("i.thumbnail", "t")
                ->andWhere("a.id = :aid")->setParameter("aid", $id)
                ->orderBy("i.sequence_number", "ASC");
            ;
            $query = $qb->getQuery();
            $data = $query->getArrayResult();
            $images = $data[0]["images"];
        }


        $frame["msg"] = sprintf("%s;;;%s", $album->getTitle(), $album->getDescription());
        $frame["data"] = $images;
        $frame["totalCount"] = count($frame["data"]);

        $json = json_encode($frame);

        return $this->jsonResponse($json);
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
