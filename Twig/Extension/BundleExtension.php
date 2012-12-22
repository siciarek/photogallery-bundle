<?php

namespace Siciarek\PhotoGalleryBundle\Twig\Extension;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use CG\Core\ClassUtils;


class BundleExtension extends \Twig_Extension
{
    protected $loader;
    protected $controller;

    public function __construct(FilesystemLoader $loader, UrlGeneratorInterface $generator)
    {
        $this->loader    = $loader;
        $this->generator = $generator;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'bundle_extension';
    }

    /**
     * Filters declaration
     */
    public function getFilters()
    {
        return array(
            'var_dump'   => new \Twig_Filter_Function('var_dump'),
            "groupstostr"  => new \Twig_Filter_Method($this, "groupstostr", array("is_safe" => array("html"))),
        );
    }

    /**
     * Functions declaration
     */
    public function getFunctions()
    {
        return array(
            "url_for"  => new \Twig_Function_Method($this, "url_for", array("is_safe" => array("html"))),
            "link_to"  => new \Twig_Function_Method($this, "link_to", array("is_safe" => array("html"))),
            "initials"  => new \Twig_Function_Method($this, "initials", array("is_safe" => array("html"))),
        );
    }

    // Specific implementations
    // var_dump is native PHP function so does not need implementation

    public function initials($initials, $tree, $url) {
        $output[] = "<div id=\"initials\">";

        foreach($initials as $i) {
            $output[] = sprintf('<div onclick="reloadlist(this, \'%s\', \'%s\')">%s</div>', $tree, $url, $i);
        }

        $output[] = "</div>";
        $output[] = '<div style="clear:both">';

        return implode("\n", $output);
    }

    public function groupstostr($groups) {
        $sep = ", ";
        $out = array();
        foreach($groups as $group) {
            $out[] = $group->getName();
        }
        return implode($sep, $out);
    }

    public function url_for($internal_uri, $parameters = array(), $absolute = false)
    {
        if (is_bool($parameters)) {
            $absolute   = $parameters;
            $parameters = array();
        }

        try {
            $url = $this->generator->generate($internal_uri, $parameters, $absolute);

        } catch (Exception $e) {
            $url = $internal_uri;
        }

        return $url;
    }

    public function link_to($name, $routeName = null, $params = array(), $options = array())
    {
        $url = $name;

        if ($routeName !== null) {
            try {
                $url = $this->url_for($routeName, $params);
            }
            catch(\Exception $e) {
                $expected = 'Route "' . $routeName . '" does not exist.';
                if($expected === $e->getMessage()) {
                    $url = $routeName;
                }
                else
                {
                    throw $e;
                }
            }
        }

        $stroptions = "";

        if (!array_key_exists("title", $options)) {
            $options["title"] = $name;
        }

        if($url === $name) {
            unset($options["title"]);
        }

        foreach ($options as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $stroptions .= sprintf(' %s="%s"', $key, $value);
        }

        return sprintf('<a href="%s"%s>%s</a>', $url, $stroptions, $name);
    }
}
