photogallery-bundle
===================

Photo Gallery Bundle for Symfony 2 projects

/composer.json

    "require": {
        "stof/doctrine-extensions-bundle": "dev-master",
        "gedmo/doctrine-extensions": "dev-master",
        "stfalcon/tinymce-bundle": "dev-master",

        "siciarek/photogallery-bundle": "dev-master"
    }

/app/config/config.yml

assetic:
    debug:          "%kernel.debug%"
    use_controller: false
    bundles:
      - FOSUserBundle
      - SiciarekPhotoGalleryBundle

fos_user:
    db_driver: orm
    firewall_name: main
    user_class: Siciarek\PhotoGalleryBundle\Entity\User

fos_js_routing:
    routes_to_expose: [\w+]

/app/config/routing.yml

siciarek_photogallery_annotation:
    resource: "@SiciarekPhotoGalleryBundle/Controller/"
    type:     annotation
    prefix:   /photogallery


/app/AppKernel.php

    new FOS\UserBundle\FOSUserBundle(),
    new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
    new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
    new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
    new Siciarek\PhotoGalleryBundle\SiciarekPhotoGalleryBundle(),
