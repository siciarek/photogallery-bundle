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
     * @Route("/album-list.json", name = "_photogallery_api_album_list")
     * @Template()
     */
    public function albumListAction()
    {
        $config  = $this->container->getParameter("siciarek_photo_gallery.config");
        $this->request = Request::createFromGlobals();
        $this->doctrine = $this->getDoctrine();
        $this->em = $this->doctrine->getEntityManager();

        $qb = $this->em->createQueryBuilder();

        $qb->select("a.id", "a.title", "a.description", "c.path as cover")
            ->from("SiciarekPhotoGalleryBundle:Album", "a")
            ->leftJoin("a.cover", "c")
            ->orderBy("a.sequence_number", "ASC");
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
        $config  = $this->container->getParameter("siciarek_photo_gallery.config");

        $json =<<<JSON
        {
            "success": true,
            "type": "data",
            "datetime": "1966-10-21 15:10:00",
            "msg": "Simply",
            "totalCount": 7,
            "data": [
                {
                    "width" : 640,
                    "height": 427,
                    "image" : "/uploads/photogallery/01/images/01.jpg",
                    "thumbnail" : "/uploads/photogallery/01/images/01.jpg"
                },
                {
                    "width" : 320,
                    "height": 480,
                    "image" : "/uploads/photogallery/01/images/02.jpg",
                    "thumbnail" : "/uploads/photogallery/01/images/02.jpg"
                },
                {
                    "width" : 480,
                    "height": 480,
                    "image" : "/uploads/photogallery/01/images/03.jpg",
                    "thumbnail" : "/uploads/photogallery/01/images/03.jpg"
                },
                {
                    "width" :640,
                    "height": 427,
                    "image" : "/uploads/photogallery/01/images/04.jpg",
                    "thumbnail" : "/uploads/photogallery/01/images/04.jpg"
                },
                {
                    "width" :640,
                    "height": 427,
                    "image" : "/uploads/photogallery/01/images/05.jpg",
                    "thumbnail" : "/uploads/photogallery/01/images/05.jpg"
                },
                {
                    "width" :320,
                    "height": 480,
                    "image" : "/uploads/photogallery/01/images/06.jpg",
                    "thumbnail" : "/uploads/photogallery/01/images/06.jpg"
                },
                {
                    "width" :640,
                    "height": 427,
                    "image" : "/uploads/photogallery/01/images/07.jpg",
                    "thumbnail" : "/uploads/photogallery/01/images/07.jpg"
                }
            ]
        }
JSON;

        return $this->jsonResponse($json);
    }

    protected function jsonResponse($json) {
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
