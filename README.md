# Ecommerce Core Bundle

Question? Bug? Feature request? Feedback? [Please don’t hesitate to open an issue - I will get back to you asap!](https://github.com/ecommerce/EcommerceCoreBundle/issues)


## Introduction

This bundle is a base to build a Symfony2 ecommerce application. It focuses on solutions with a high degree of customization. It’s a toolset to implement your own business and application logic. If you’re looking for a 'out of the box' webshop you should rather choose a different solution like [Sylius](http://Sylius.org) or [Vespolina](https://github.com/vespolina). It takes care of persistence and you can optionally use the RESTful controllers for the products and the cart. You write your own product handler (or several ones if your project requires it) which defines the product properties (as many as you want and translatable without having to update the database thanks to [PHPCR](http://phpcr.github.io/)), product options, cart item validation and product availability. You can hook into all different kinds of events to adapt the provided application flow to your needs.



## Status

! Work in progress - Not Ready for Production Use !

It currently doesn’t provide a default product handler so you have to write your own. I haven’t decided yet whether to provide a default one or just create a tutorial for that (just look at the [ecommerce-sandbox](https://github.com/ecommerce/ecommerce-sandbox) for now). For simplicity reasons it currently contains features which are not needed by everyone (like e.g. translatable product properties, a REST controller). They will be extracted to seperate libraries or bundles in the future.

The first webshop based on this bundle is ready to launch. The shop allows users to rent different kinds of products (using a custom availability check for a product in different sizes and a different rental durations) and buy other items.


## Services and classes

Service id                                                     | Class
-------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
ecommerce_core.cart.controller                                 | [Ecommerce\Bundle\CoreBundle\Controller\CartController](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Controller/CartController.php)
ecommerce_core.product.controller                              | [Ecommerce\Bundle\CoreBundle\Controller\ProductController](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Controller/ProductController.php)
ecommerce_core.cart.manager                                    | [Ecommerce\Bundle\CoreBundle\Cart\Manager](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Cart/Manager.php)
ecommerce_core.cart.repository                                 | [Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartRepository](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Doctrine/Orm/CartRepository.php)
ecommerce_core.cart_item.repository                            | [Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartItemRepository](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Doctrine/Orm/CartItemRepository.php)
ecommerce_core.event_listener.doctrine_orm_subscriber          | [Ecommerce\Bundle\CoreBundle\EventListener\DoctrineOrmSubscriber](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/EventListener/DoctrineOrmSubscriber.php)
ecommerce_core.event_listener.doctrine_phpcr_subscriber        | [Ecommerce\Bundle\CoreBundle\EventListener\DoctrinePhpcrSubscriber](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/EventListener/DoctrinePhpcrSubscriber.php)
ecommerce_core.product.form.type.translated_property           | [Ecommerce\Bundle\CoreBundle\Product\Form\Type\TranslatedPropertyType](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Product/Form/Type/TranslatedPropertyType.php)
ecommerce_core.product.handler_manager                         | [Ecommerce\Bundle\CoreBundle\Product\HandlerManager](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Product/HandlerManager.php)
ecommerce_core.product.manager                                 | [Ecommerce\Bundle\CoreBundle\Product\Manager](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Product/Manager.php)
ecommerce_core.product.repository                              | [Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\ProductRepository](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Doctrine/Phpcr/ProductRepository.php)
ecommerce_core.product_reference.repository                    | [Ecommerce\Bundle\CoreBundle\Doctrine\Orm\ProductReferenceRepository](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Doctrine/Orm/ProductReferenceRepository.php)
ecommerce_core.twig.ecommerce_extension                        | [Ecommerce\Bundle\CoreBundle\Twig\EcommerceExtension](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Twig/EcommerceExtension.php)
ecommerce_core.controller_utils                                | [Ecommerce\Bundle\CoreBundle\Util\ControllerUtils](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Util/ControllerUtils.php)

Use the tag ``ecommmerce.product_handler`` to register your [product handler](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Product/ProductHandlerInterface.php).

[Products (Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product)](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Doctrine/Phpcr/Product.php) are stored in PHPCR but a simple [ProductReference (Ecommerce\Bundle\CoreBundle\Doctrine\Orm\ProductReference)](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Doctrine/Orm/ProductReference.php) entity is stored in ORM so it can be associated with a [CartItem (Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartItem)](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Doctrine/Orm/CartItem.php). The [Cart (Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartItem)](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Doctrine/Orm/Cart.php) entity is very basic for now but will be updated soon to include subtotals, discounts, tax information etc.

To create a form type for a product use the 
[Ecommerce\Bundle\CoreBundle\Product\Form\DataMapper\ProductDataMapper](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Product/Form/DataMapper/ProductDataMapper.php) as the DataMapper (``$builder->setDataMapper(new ProductDataMapper());`` in the ``buildForm`` method) and the ``ecommerce_type_translated_property`` form type for product properties that should be translatable. Those properties can then be rendered using the twig function ``translate_property(product, property)``.



## Installation


    php composer.phar require ecommerce/core-bundle:dev-master

Add the following lines to ```app/AppKernel.php``` (assuming your project already includes the bundles from Symfony Framework Standard Edition):

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

You can check out the [ecommerce-sandbox](https://github.com/ecommerce/ecommerce-sandbox) to see how it should look like.


## Next

- Examples (2+) in the sandbox / tutorial
- Create the Sonata admin bundle which allows you to define product properties through extensions
- Create the Elasticsearch/Elastica bundle
- Add checkout event system
- Testing (decide on testing methods)
- Move the REST controllers to a separate bundle
- Move translatable product properties to a separate bundle

Feel free to open an issue for any feature request!



## License

This bundle is under the MIT license. See the complete license in the bundle:

[Resources/meta/LICENSE](https://github.com/ecommerce/EcommerceCoreBundle/blob/master/Resources/meta/LICENSE)


Please note that it requires other libraries to run (see [Packagist](https://packagist.org/packages/ecommerce/core-bundle)) and not all of them are MIT-licensed (like Apache 2.0).
