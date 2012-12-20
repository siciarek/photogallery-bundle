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
    protected $config;
    protected $output;
    protected $request;

    public function preExecute()
    {
        $this->config = $this->container->getParameter("siciarek_photo_gallery.config");
        $this->request = $this->getRequest();
        $cookies = $this->request->cookies->all();

        $this->output = array(
            "page_style" => $this->config["style"],
            "title"      => $this->config["title"],
            "settings"   => new \stdClass(),
        );

        if (array_key_exists("settings", $cookies)) {
            $this->output["settings"] = json_decode($cookies["settings"], true);
        }
    }

    /**
     * @Route("/settings.html", name = "_settings")
     * @Template()
     */
    public function settingsAction()
    {

        return $this->output;
    }

    /**
     * @Route("/{id}/{slug}.html", name = "_album", requirements = {"id"="^[1-9]\d*$", "slug"="^\S+$"})
     * @Template()
     */
    public function photosAction($id, $slug)
    {
        $this->output["id"] = $id;
        return $this->output;
    }

    /**
     * @Route("/", name="_albums")
     * @Template()
     */
    public function albumsAction()
    {
        return $this->output;
    }
}
