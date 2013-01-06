<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jsiciarek
 * Date: 31.12.12
 * Time: 16:04
 * To change this template use File | Settings | File Templates.
 */
namespace Siciarek\PhotoGalleryBundle\Composer;

class ScriptHandler
{
    public static function installSubmodules($event)
    {
        chdir(__DIR__ . "/..");
        echo `git submodule update --init`;
    }

    public static function updateSubmodules($event)
    {
        chdir(__DIR__ . "/..");
        echo `git submodule foreach git pull origin master`;
    }
}
