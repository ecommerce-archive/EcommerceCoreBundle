<?php

namespace Ecommerce\Bundle\CoreBundle\EventListener;


use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product;

class DoctrineOrmEventSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ProductManager
     */
    protected $productManager;

    protected $document;


    /**
     * Constructor.
     *
     * @param ContainerInterface $container The service container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container      = $container;
    }


    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'postPersist',
        );
    }


    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Product) {
            if (null === $entity->getProperties()) {
                $this->document = $this->getProductManager()->autocreatePropertiesDocument($entity);
            } elseif ($entity->getProperties()->getProduct() !== $entity) {
                $this->document = $entity->getProperties();
            }

            return;
        }
//        if ($entity instanceof ProductCategoryMapping) {
//            $em = $args->getEntityManager();
//            $entity;
//        }
    }


    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Product) {
            if ($this->document instanceof ProductProperties) {
                $this->getProductManager()->setProductPropertiesEntityReference($entity, $this->document);
            }
        }
    }


    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        $changes = $args->getEntityChangeSet();
    }


    public function postUpdate(LifecycleEventArgs $args)
    {
    }

    protected function getProductManager()
    {
        if (null !== $this->productManager) {
            return $this->productManager;
        }

        $this->productManager = $this->container->get('glamour_rent_app.product_manager');

        return $this->productManager;
    }
}
