<?php

class ApiTest extends PHPUnit_Framework_TestCase
{
    private $om;

    function setUp() {
        $this->om = new \Doctrine\Common\Persistence\ObjectManager();
    }

    public function testAlbums()
    {
        $users = array(
            "jsiciarek" => $this->om->getRepository("SiciarekPhotoGalleryBundle:User")->findOneBy(array("username" => "jsiciarek")),
        );

        foreach($users as $key => $val) {
            $this->assertNotNull($val);
            $this->assertEquals($key, $val->getUsername());
        }
    }
}

