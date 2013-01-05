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
        $gitdir = __DIR__ . "/../.git";
        $gitcmd = "git --git-dir=$gitdir ";
        $command = $gitcmd . " submodule update --init";
        echo "START INSTALLATION\n";
        echo `$command`;
        echo "START INSTALLATION\n";
    }

    public static function updateSubmodules($event)
    {
        $gitdir = __DIR__ . "/../.git";
        $gitcmd = "git --git-dir=$gitdir ";
        $command = $gitcmd . " submodule foreach pull";
        echo "START UPDATE\n";
        echo `$command`;
        echo "UPDATE COMPLETE\n";
    }
}
