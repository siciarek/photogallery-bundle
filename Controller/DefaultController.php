<?php

namespace Siciarek\PhotoGalleryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Post controller.
 *
 * @Route("/photogallery")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/{id}/{slug}.html", name = "_album", requirements = {"id"="^[1-9]\d*$", "slug"="^\S+$"})
     * @Template()
     */
    public function albumAction($id, $slug)
    {
        $config  = $this->container->getParameter("siciarek_photo_gallery.config");

        return array(
            "page_style" => $config["style"],
            "title" => $config["title"],
            "id" => $id,
        );
    }

    /**
     * @Route("/", name="_albums")
     * @Template()
     */
    public function indexAction()
    {
        $config  = $this->container->getParameter("siciarek_photo_gallery.config");

        return array(
            "page_style" => $config["style"],
            "title" => $config["title"],
            "subtitle" => "Albums",
        );
    }
}
