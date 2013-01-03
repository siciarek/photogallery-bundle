<?php

namespace Siciarek\PhotoGaleryBundle\DataFixtures\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Siciarek\PhotoGalleryBundle\Entity as E;
use PDO;


class BaseFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    protected static $go = true;

    public function getOrder()
    {
        return 0;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $em)
    {
        $config = $this->container->getParameter("siciarek_photo_gallery.config");
        self::$go = $config["load_fixtures"];
    }
}