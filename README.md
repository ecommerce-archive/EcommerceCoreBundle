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

before ``return $loader;`` in ```app/autoload.php```. 


Update the database by running (not recommended for production):

    php app/console doctrine:phpcr:init:dbal
    php app/console doctrine:phpcr:workspace:create default
    php app/console doctrine:phpcr:repository:init
    php app/console doctrine:schema:update --force


### License: MIT

See [``Resources/meta/LICENSE``](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Resources/meta/LICENSE) file.
