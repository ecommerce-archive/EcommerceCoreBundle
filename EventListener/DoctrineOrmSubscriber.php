<?php

namespace Ecommerce\Bundle\CoreBundle\EventListener;


use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product;

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
//            'prePersist',
//            'preUpdate',
        );
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
}
