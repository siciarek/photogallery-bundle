.. code-block:: php

    private $locale;

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }



Configuration notes:
====================


/composer.json  (add)
--------------------------------------------------------------------------------

    "require": {
        "doctrine/data-fixtures": "dev-master",
        "doctrine/doctrine-fixtures-bundle": "dev-master",
        "stof/doctrine-extensions-bundle": "dev-master",
        "gedmo/doctrine-extensions": "dev-master",
        "stfalcon/tinymce-bundle": "dev-master",

        "siciarek/photogallery-bundle": "dev-master"
    }

/app/AppKernel.php (add)
--------------------------------------------------------------------------------


    $bundles[] = new FOS\UserBundle\FOSUserBundle();
    $bundles[] = new FOS\JsRoutingBundle\FOSJsRoutingBundle();
    $bundles[] = new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle();
    $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
    $bundles[] = new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle();
    $bundles[] = new Siciarek\PhotoGalleryBundle\SiciarekPhotoGalleryBundle();


/app/config/config.yml (change)
--------------------------------------------------------------------------------
framework:
    # uncomment:
    translator:      { fallback: "%locale%" }

assetic:
    debug:          "%kernel.debug%"
    use_controller: false
    bundles:        [ FOSUserBundle, SiciarekPhotoGalleryBundle ]


/app/config/config.yml (add)
--------------------------------------------------------------------------------

fos_user:
    db_driver: orm
    firewall_name: main
    user_class: Siciarek\PhotoGalleryBundle\Entity\User

fos_js_routing:
    routes_to_expose: [\w+]

stof_doctrine_extensions:
    default_locale: %locale%
    translation_fallback: true
    orm:
        default:
            sluggable: true
            timestampable: true
            translatable: true

/app/config/routing.yml  (add)
--------------------------------------------------------------------------------

siciarek_photogallery_annotation:
    resource: "@SiciarekPhotoGalleryBundle/Controller/"
    type:     annotation
    prefix:   /photogallery

# Following FOS routes are required:

fos_js_routing:
    resource: "@FOSJsRoutingBundle/Resources/config/routing/routing.xml"

fos_user_group:
    resource: "@FOSUserBundle/Resources/config/routing/group.xml"
    prefix: /group

fos_user_security:
    resource: "@FOSUserBundle/Resources/config/routing/security.xml"

fos_user_profile:
    resource: "@FOSUserBundle/Resources/config/routing/profile.xml"
    prefix: /profile

fos_user_register:
    resource: "@FOSUserBundle/Resources/config/routing/registration.xml"
    prefix: /register

fos_user_resetting:
    resource: "@FOSUserBundle/Resources/config/routing/resetting.xml"
    prefix: /resetting

fos_user_change_password:
    resource: "@FOSUserBundle/Resources/config/routing/change_password.xml"
    prefix: /profile

/app/config/security.yml  (change)
--------------------------------------------------------------------------------

security:
    providers:
        fos_userbundle:
            id: fos_user.user_provider.username_email

    encoders:
        FOS\UserBundle\Model\UserInterface: sha512

    firewalls:
        main:
            pattern: ^/
            form_login:
                provider: fos_userbundle
                csrf_provider: form.csrf_provider
            logout:       true
            anonymous:    true
            remember_me:
                key:      "%secret%"
                lifetime: 31536000  # 365 days in seconds
                remember_me_parameter: _remember_me
                path: /
                domain: ~

    access_control:
        - { path: ^/login$,        role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/register,      role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/resetting,     role: IS_AUTHENTICATED_ANONYMOUSLY }

        - { path: ^/$,             role: IS_AUTHENTICATED_ANONYMOUSLY }

    role_hierarchy:
        ROLE_USER:        IS_AUTHENTICATED_ANONYMOUSLY
        ROLE_ADMIN:       ROLE_USER


run:
--------------------------------------------------------------------------------

php app/console cache:clear
php app/console doctrine:schema:update --force
php app/console assets:install web
php app/console assetic:dump --no-debug
cp -vR web/bundles/siciarekphotogallery/images web
mkdir web\uploads
cd vendor/siciarek/photogallery-bundle/Siciarek/PhotoGalleryBundle/
git submodule init
git submodule update
cd ../../../../../
php app/console cache:clear
