<?php

namespace Ecommerce\Bundle\CoreBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Util\ClassUtils;


//use GlamourRent\AppBundle\Form\DataMapper\PreparedPropertyAccessor;
use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product;

class DoctrinePhpcrEventSubscriber implements EventSubscriber
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
//            'loadClassMetadata',
            'postPersist',
            'preUpdate',
            'preRemove',
        );
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {

        $dm = $args->getObjectManager();

        /** @var ClassMetadata $classMetadata */
        $classMetadata = $args->getClassMetadata();

        if ($classMetadata->getName() !== 'GlamourRent\AppBundle\Doctrine\Phpcr\ProductProperties') {
            return;
        }

        foreach (array('name', 'desc') as $fieldName) {

            $field = array(
                'fieldName'  => $fieldName,
                'type'       => 'string',
                'translated' => true,
                'name'       => null,
                'property'   => $fieldName,
                'multivalue' => false,
                'assoc'      => null,
                'nullable'   => true,
            );

            $classMetadata->mappings[$fieldName] = $field;

            $locale = $dm->getLocaleChooserStrategy()->getLocale();

            $fakeReflectionProperty = new PreparedPropertyAccessor($fieldName, $locale);

            $classMetadata->reflFields[$fieldName] = $fakeReflectionProperty;

            $classMetadata->translatableFields[] = $fieldName;
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getObject();

        if ($document instanceof Product) {

            $userId = $this->getUserId();
            if ($userId) {
                $document->node->setProperty('jcr:lastModifiedBy', $this->getUserId());
            }
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $document = $args->getObject();

        if ($document instanceof Product) {

            $this->container->get('ecommerce_core.product_reference.repository')->create($document);

            $userId = $this->getUserId();
            if ($userId) {
                $document->node->setProperty('jcr:createdBy', $this->getUserId());
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
