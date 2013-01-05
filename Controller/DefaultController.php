<?php

namespace Siciarek\PhotoGalleryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Yaml\Parser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    /**
     * Default controller.
     *
     * @Route("/")
     */
class DefaultController extends Controller
{
    protected $config;
    protected $output;
    protected $request;
    protected $session;
    protected $cookies;
    protected $locale;
    protected $translations = array();


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
    public function imagesAction($id, $slug)
    {
        return $this->output;
    }

    /**
     * @Route("/albums.html", name="_albums")
     * @Template()
     */
    public function albumsAction()
    {
        return $this->output;
    }

    /**
     * @Route("/", name="_photogallery_homepage")
     * @Template()
     */
    public function indexAction()
    {
        return $this->output;
    }

    public function preExecute()
    {
        $this->config = $this->container->getParameter("siciarek_photo_gallery.config");
        $this->request = $this->getRequest();
        $this->session = $this->request->getSession();
        $this->cookies = $this->request->cookies->all();

        $this->locale = $this->session->get("locale", $this->request->getLocale());
        $this->request->setLocale($this->locale);

        $this->output = array(
            "config"     => $this->config,
            "settings"   => new \stdClass(),
        );

        if (array_key_exists("settings", $this->cookies)) {
            $this->output["settings"] = json_decode($this->cookies["settings"], true);
        }

        $yaml = new Parser();
        $transfile = __DIR__ . "/../Resources/translations/messages.pl.yml";
        $this->output["translations"] = $yaml->parse(file_get_contents($transfile));
    }
}
