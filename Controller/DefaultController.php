<?php

namespace Siciarek\PhotoGalleryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/photos/{id}/{slug}.html", name = "_album", requirements = {"id"="^[1-9]\d*$", "slug"="^\S+$"})
     * @Template()
     */
    public function albumAction($id, $slug)
    {
        $config  = $this->container->getParameter("siciarek_photo_gallery.config");

        return array(
            "page_style" => $config["style"],
            "title" => $config["title"],
            "subtitle" => "Simply",
        );
    }

    /**
     * @Route("/photos/index.html")
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
