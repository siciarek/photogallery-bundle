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
    	$repodir = __DIR__ . "/.."; 
        $gitdir = $repodir . "/.git";
        
		$cmd = array();
        $cmd[] = "git status";
        $cmd[] = "git submodule update --init";

		echo "START INSTALLATION\n";
		chdir($repodir);
		foreach($cmd as $command) {
			echo " *** " . $command . ":\n";
			echo `$command`;
		}
        echo "INSTALLATION COMPLETE\n";
    }

    public static function updateSubmodules($event)
    {
		$repodir = __DIR__ . "/.."; 
        $gitdir = $repodir . "/.git";

		$cmd = array();
        $cmd[] = "git status";
        $cmd[] = "git submodule foreach pull";
			
		echo "START UPDATING\n";
		chdir($repodir);
		foreach($cmd as $command) {
			echo " *** " . $command . ":\n";
			echo `$command`;
		}
        echo "UPDATING COMPLETE\n";
    }
}
