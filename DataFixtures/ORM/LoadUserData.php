<?php

// src/SesCrm/UserBundle/DataFixtures/ORM/LoadUserData.php
namespace SesCrm\UserBundle\DataFixtures\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Siciarek\PhotoGalleryBundle\Entity as E;
use PDO;


class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private static $devel = true;

    public function getOrder()
    {
        return 1;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $em)
    {
        $users = array(
            array(
                "first_name" => "Jacek",
                "last_name"  => "Siciarek",
                "username"   => "jsiciarek",
                "password"   => "helloworld",
                "email"      => "siciarek@gmail.com",
            )
        );

        foreach ($users as $u) {

            $obj = new E\User();

            $obj->setFirstName($u["first_name"]);
            $obj->setLastName($u["last_name"]);
            $obj->setUsername($u["username"]);
            $obj->setPlainPassword($u["password"]);
            $obj->setEmail($u["email"]);
            $obj->setEnabled(true);

            $em->persist($obj);
        }

        $em->flush();
    }
}