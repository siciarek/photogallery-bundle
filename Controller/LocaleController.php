<?php

namespace Siciarek\PhotoGalleryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query;


/**
 * Locale controller.
 *
 * @Route("/photogallery")
 */
class LocaleController extends Controller
{

    /**
     * @Route("/lang-{locale}.html", name="_photogallery_change_locale", defaults={"locale"="pl"}, requirements = {"locale"="^[a-z]{2}$"})
     */
    public function setLocaleAction($locale) {

        $locale = in_array($locale, array("en", "pl")) ? $locale : "en";

        $session = $this->getRequest()->getSession();
        $session->set("locale", $locale);

        $referer = $this->getRequest()->server->get('HTTP_REFERER');
        $referer = $referer == null ? $this->generateUrl("_photogallery_home") : $referer;

        return $this->redirect($referer);
    }
}
