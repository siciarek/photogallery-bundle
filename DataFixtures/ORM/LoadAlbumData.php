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


class LoadAlbumData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private static $devel = true;

    public function getOrder()
    {
        return 2;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $em)
    {
        $albums = array(
            "jsiciarek" => array(
                "The Fly", "The Sea", "My Adventure", "Hot & Cold", "Stars Fell on Alabama", "Who Let the Dogs Out?",
            ),
            "colak"     => array(
                "Lemon Dance", "Strange Butterfly", "Cooking and Fun",
            ),
        );

        $o = 0;

        foreach ($albums as $u => $alb) {
            $user = $this->getReference($u);
            $creator = new E\Creator();
            $creator->setUsername($user->getUsernameCanonical());
            $creator->setEmail($user->getEmailCanonical());
            $creator->setFirstName($user->getFirstName());
            $creator->setLastName($user->getLastName());
            $em->persist($creator);

            foreach ($alb as $a) {
                $title = sprintf("%s", $a);
                $description = sprintf("Description of the %s Album created by %s %s.", $a, $creator->getFirstName(), $creator->getLastName());

                $obj = new E\Album();
                $obj->setIsVisible(true);
                $obj->setSequenceNumber($o++);
                $obj->setTitle($title);
                $obj->setDescription($description);
                $obj->setCreator($creator);

                $em->persist($obj);
            }
        }

        $em->flush();
    }
}