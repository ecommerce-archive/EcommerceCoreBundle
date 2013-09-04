<?php

namespace Ecommerce\Bundle\CoreBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product;

class DoctrinePhpcrSubscriber implements EventSubscriber
{
    protected $productType;

    protected $productProvider;

    protected $userId;

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
        $this->container = $container;
    }


    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'preUpdate',
            'preRemove',
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $document = $args->getObject();

        if ($document instanceof Product) {

            $this->container->get('ecommerce_core.product_reference.repository')->create($document);

            if ($userId = $this->getUserId()) {
                $document->node->setProperty('jcr:createdBy', $this->getUserId());
                $document->node->setProperty('jcr:lastModifiedBy', $this->getUserId());
            }
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getObject();

        if ($document instanceof Product) {

            if ($userId = $this->getUserId()) {
                $document->node->setProperty('jcr:lastModifiedBy', $this->getUserId());
            }
        }
    }


    public function preRemove(LifecycleEventArgs $args)
    {
        $document = $args->getObject();

        if ($document instanceof Product) {
            $this->container->get('ecommerce_core.product_reference.repository')->delete($document);
        }
    }


    private function getUserId()
    {
        if ($this->userId !== null) {
            return $this->userId;
        }

        if (!$this->container->has('security.context')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');

        if ((null === $token = $securityContext->getToken())
            || !is_object($user = $token->getUser())
        ) {
            return null;
        }

        return $this->userId = $user->getId();
    }
}
