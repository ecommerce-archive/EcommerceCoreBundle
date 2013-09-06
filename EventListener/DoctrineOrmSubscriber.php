<?php

namespace Ecommerce\Bundle\CoreBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\ProductReference;

class DoctrineOrmSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    protected $container;


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
            'postLoad',
//            'prePersist',
//            'preUpdate',
        );
    }


    public function postLoad(LifecycleEventArgs $args)
    {
        if ($args->getEntity() instanceof ProductReference) {
            $this->addProduct($args->getEntity());
        }
    }


    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof Cart) {
            return;
        }
    }


    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        $changes = $args->getEntityChangeSet();
    }



    private function addProduct(ProductReference $productReference)
    {
        $productRepo = $this->container->get('ecommerce_core.product.repository');
        $productReference->setProduct($productRepo->getReference($productReference->getId()));
    }
}
