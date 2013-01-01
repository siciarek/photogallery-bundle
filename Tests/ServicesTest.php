<?php

class ServicesTest extends PHPUnit_Framework_TestCase
{
    public function testWatermark()
    {
        $image = __DIR__ . "/images/myband.jpg";
        $this->assertTrue(file_exists($image));
        $this->assertTrue(is_readable($image));
        $this->assertTrue(is_writable($image));
    }
}

