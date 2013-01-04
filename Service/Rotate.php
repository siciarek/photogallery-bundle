<?php
namespace Siciarek\PhotoGalleryBundle\Service;

class Rotate implements ImageFilterInterface
{
    public $angle = 0;
    protected $config = array();

    public function __construct($kernel, $angle)
    {
        $this->config = $kernel->getContainer()->getParameter("siciarek_photo_gallery.config");
        $this->angle = intval($angle);
    }

    public function apply($image_path)
    {

    }

    private function imagecreatefrom($path, $mime_type) {
        switch($mime_type) {
            case "image/jpg":
            case "image/jpeg":

                return imagecreatefromjpeg($path);
            case "image/png":
                return imagecreatefrompng($path);

        }
    }

    private function saveimage($image, $mime_type, $path, $quality = 100) {
        switch($mime_type) {
            case "image/jpg":
            case "image/jpeg":

                return imagejpeg($image, $path, $quality);
            case "image/png":
                return imagepng($image, $path);

        }
    }

    public function xapply(\Siciarek\PhotoGalleryBundle\Entity\File $file)
    {
        $success = false;
        $src_path = $this->config["uploads_directory"] . $file->getPath();


        if ($this->angle !== 0 and file_exists($src_path) and is_readable($src_path) and is_writable($src_path)) {

            $src_image = $this->imagecreatefrom($src_path, $file->getMimeType());



            $trg_image = imagerotate($src_image, $this->angle, 0);

            if ($trg_image !== false) {
                $success = $this->saveimage($trg_image, $file->getMimeType(), $src_path);
            }

            imagedestroy($src_image);
            imagedestroy($trg_image);
        }

        if($success === false) {
            throw new \Exception("Image can not be modified.");
        }

        $width = $file->getWidth();
        $height = $file->getHeight();

        $file->setWidth($height);
        $file->setHeight($width);
        $file->setFileSize(filesize($src_path));
    }
}

