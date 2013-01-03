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


class LoadUserData extends BaseFixture
{
    public function getOrder()
    {
        return 1;
    }

    public function load(ObjectManager $em)
    {
        if(self::$go === false) {
            return;
        }

        $users = array(
            array(
                "first_name"  => "Jacek",
                "last_name"   => "Siciarek",
                "username"    => "jsiciarek",
                "password"    => "helloworld",
                "email"       => "siciarek@gmail.com",
                "description" => "PhotoGalleryBundle Lead Developer.",
            ),
            array(
                "first_name"  => "Czesław",
                "last_name"   => "Olak",
                "username"    => "colak",
                "password"    => "helloworld",
                "email"       => "siciarek@hotmail.com",
                "description" => "Mayor of Świecie.",
            ),
        );

        foreach ($users as $u) {

            $obj = new E\User();

            $obj->setFirstName($u["first_name"]);
            $obj->setLastName($u["last_name"]);
            $obj->setUsername($u["username"]);
            $obj->setPlainPassword($u["password"]);
            $obj->setEmail($u["email"]);
            $obj->setDescription($u["description"]);
            $obj->setEnabled(true);

            $em->persist($obj);

            $this->setReference($u["username"], $obj);
        }

        $em->flush();
    }
}