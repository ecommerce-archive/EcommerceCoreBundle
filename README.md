# Ecommerce Core Bundle

## Description

This bundle provides a product and cart model. Products are saved in [PHPCR](http://phpcr.github.io/). The cart uses Doctrine ORM.

## Installation



    php composer.phar require ecommerce/core-bundle:dev-master

Add the following lines to ```app/AppKernel.php```:

                new Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle(),
                new Ecommerce\Bundle\CoreBundle\EcommerceCoreBundle(),

Add

    AnnotationRegistry::registerFile(__DIR__.'/../vendor/doctrine/phpcr-odm/lib/Doctrine/ODM/PHPCR/Mapping/Annotations/DoctrineAnnotations.php');

to ```app/autoload.php```.



Edit ```app/config/config.yml``` so it looks like:

    doctrine:
        orm:
            auto_generate_proxy_classes: %kernel.debug%
            entity_managers:
                default:
                    auto_mapping: true
                    mappings:
                        EcommerceCoreBundle:
                            type: xml
                            dir: Resources/config/doctrine-orm
                            prefix: Ecommerce\Bundle\CoreBundle\Doctrine\Orm

    doctrine_phpcr:
        session:
            backend: %phpcr_backend%
            workspace: %phpcr_workspace%
            username: %phpcr_user%
            password: %phpcr_pass%
        odm:
            document_managers:
                default:
                    auto_mapping: true
                    mappings:
                        EcommerceCoreBundle:
                            type: xml
                            dir: Resources/config/doctrine-phpcr
                            prefix: Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr

To try the bundle in development, just run:

    php app/console doctrine:phpcr:init:dbal
    php app/console doctrine:phpcr:workspace:create default
    php app/console doctrine:phpcr:repository:init
    php app/console doctrine:schema:update --force