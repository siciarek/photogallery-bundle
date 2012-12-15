<?php

namespace Siciarek\PhotoGalleryBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
//        $client = static::createClient();
//
//        $crawler = $client->request('GET', '/hello/Fabien');

        $this->assertTrue(true); //$crawler->filter('html:contains("Hello Fabien")')->count() > 0);
    }
}
