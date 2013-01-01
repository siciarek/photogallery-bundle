<?php

namespace Siciarek\PhotoGalleryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $defaults = $this->getDefaults();

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root("siciarek_photo_gallery", "array");

        $rootNode->children()
            ->scalarNode("title")
            ->defaultValue($defaults["title"])
            ->end()
            ->scalarNode("homepage")
            ->defaultValue($defaults["homepage"])
            ->end()
            ->scalarNode("style")
            ->defaultValue($defaults["style"])
            ->end()
            ->scalarNode("default_cover")
            ->defaultValue($defaults["default_cover"])
            ->end()
            ->scalarNode("watermark")
            ->defaultValue($defaults["watermark"])
            ->end()
            ->scalarNode("uploads_directory")
            ->defaultValue($defaults["uploads_directory"])
            ->end()
            ->arrayNode("thumbnails")
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode("cache")->defaultTrue()->end()
            ->scalarNode("format")->defaultValue($defaults["thumbnails"]["format"])->end()
            ->scalarNode("width")->defaultValue($defaults["thumbnails"]["width"])->end()
            ->scalarNode("height")->defaultValue($defaults["thumbnails"]["height"])->end()
            ->end();

        return $treeBuilder;
    }


    /**
     * Get default configuration of the each instance of editor
     *
     * @return array
     */
    private function getDefaults()
    {
        return array(
            "title"             => "Photo Gallery",
            "homepage"          => "_photogallery_homepage",
            "style"             => "/bundles/siciarekphotogallery/css/photogallery.css",
            "watermark"         => "%kernel.root_dir%/../web/bundles/siciarekphotogallery/images/watermark.png",
            "default_cover"     => "/bundles/siciarekphotogallery/images/default-cover.png",
            "uploads_directory" => "%kernel.root_dir%/../web/uploads/photogallery",
            "thumbnails"        => array(
                "format" => "png",
                "width"  => 150,
                "height" => 100
            ),
        );
    }
}
