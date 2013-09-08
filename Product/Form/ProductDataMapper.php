<?php

namespace Ecommerce\Bundle\CoreBundle\Product\Form;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

use Jackalope\Node;

class ProductDataMapper implements DataMapperInterface
{
    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    /**
     * Constructor.
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }


    public function mapDataToForms($data, $forms)
    {
        if (null === $data || array() === $data) {
            return;
        }

        if (!is_array($data) && !is_object($data)) {
            throw new UnexpectedTypeException($data, 'object, array or empty');
        }

//        $nodePath = new PropertyPath('node');
//        $node = $this->propertyAccessor->getValue($data, $nodePath);

        $node = $data->getNode();

        if (!$node instanceof Node) {
            throw new UnexpectedTypeException($data, 'Jackalope\\Node');
        }

        foreach ($forms as $form) {
            $propertyPath = $form->getPropertyPath();
            $config = $form->getConfig();

            if (null !== $propertyPath && $config->getMapped()) {
                $form->setData($node->getPropertyValueWithDefault(strval($propertyPath), null));
            }
        }
    }

    public function mapFormsToData($forms, &$data)
    {
        if (null === $data) {
            return;
        }

        if (!is_array($data) && !is_object($data)) {
            throw new UnexpectedTypeException($data, 'object, array or empty');
        }

//        $nodePath = new PropertyPath('node');
//        $node = $this->propertyAccessor->getValue($data, $nodePath);

        $node = $data->getNode();

        if (!$node instanceof Node) {
            throw new UnexpectedTypeException($data, 'Jackalope\\Node');
        }

        foreach ($forms as $form) {
            $propertyPath = $form->getPropertyPath();
            $config = $form->getConfig();

            if (null !== $propertyPath && $config->getMapped() && $form->isSynchronized() && !$form->isDisabled()) {
                if (!is_object($data) || !$config->getByReference() || $form->getData() !== $node->getPropertyValueWithDefault(strval($propertyPath), null)) {
                    $node->setProperty(strval($propertyPath), $form->getData());
                }
            }
        }
    }
}
