<?php
namespace Siciarek\PhotoGalleryBundle\Service;

interface ImageFilterInterface
{
    public function apply($image_path);
}

