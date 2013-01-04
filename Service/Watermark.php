<?php
namespace Siciarek\PhotoGalleryBundle\Service;

class Watermark implements ImageFilterInterface
{
    public $alpha = 0;
    protected $config = array();

    public function __construct($kernel, $alpha)
    {
        $this->config = $kernel->getContainer()->getParameter("siciarek_photo_gallery.config");
        $this->alpha = intval($alpha);
    }

    public function apply($image_path)
    {
        $watermark_path = $this->config["watermark"];

        if ($this->alpha > 0 and $watermark_path !== null and file_exists($watermark_path) and is_readable($watermark_path)) {

            $watermark = imagecreatefrompng($watermark_path);
            $watermark_width = (int)imagesx($watermark);
            $watermark_height = (int)imagesy($watermark);

            $src_image = imagecreatefromjpeg($image_path);
            $src_image_width = (int)imagesx($src_image);
            $src_image_height = (int)imagesy($src_image);
            imagealphablending($src_image, true);

            $dest_x = intval(($src_image_width - $watermark_width) / 2);
            $dest_y = intval(($src_image_height - $watermark_height) / 2);

            imagecopymerge($src_image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $this->alpha);
            imagejpeg($src_image, $image_path, 100);
            imagedestroy($watermark);
            imagedestroy($src_image);
        }
    }
}

