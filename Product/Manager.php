<?php

namespace Ecommerce\Bundle\CoreBundle\Product;

use PHPCR\Util\NodeHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Document\Generic;

use PHPCR\ItemExistsException;

use Jackalope\Factory;
use Jackalope\Node;

use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product;


class Manager
{
    private $dm;

    private $basepath;

    /** @var Generic */
    private $productNode;

    private $container;

    private $products;

    /**
     * Constructor.
     */
    public function __construct(DocumentManager $dm, $basepath, ContainerInterface $container)
    {
        $this->dm        = $dm;
        $this->basepath  = $basepath;
        $this->container = $container;
    }


    /**
     * @param bool $name
     * @param bool $withNode
     * @return null|Product
     */
    public function createProduct($name = false, $withNode = false)
    {
        $product = new Product();
        $product->setParent($this->getProductNode());

        if (strlen($name)) {
            if ($this->getProductNode()->getNode()->hasNode($name)) {
                return null;
            }
        } else {
            $name = sha1(mt_rand());
        }

        $product->setNodename($name);
        $product->setName($name);

        if ($withNode) {
            $product->node = new Node(
                new Factory(),
                new \stdClass(),
                $this->getProductNode()->getId().'/'.$product->getNodename(),
                $this->dm->getPhpcrSession(),
                $this->dm->getPhpcrSession()->getObjectManager()
            );
        }

        return $product;
    }

    public function save(Product $product)
    {
        try {
            $this->dm->persist($product);
            $this->dm->flush();

            return true;
        } catch (ItemExistsException $e) {
            return false;
        }
    }

    public function delete(Product $product)
    {
        try {
            $this->dm->remove($product);
            $this->dm->flush();

            return true;
        } catch (ItemExistsException $e) {
            return false;
        }
    }

    /**
     * @param string $id
     * @return Product|null
     */
    public function find($id)
    {
        return $this->dm->find(null, $id);
    }

    /**
     * @param bool $reload
     * @return Product[]
     */
    public function findAll($reload = false)
    {
        if ($this->products !== null & !$reload) {

            return $this->products;
        }

        return $this->products = $this->getProductNode()->getChildren();
    }


    /**
     * @param Product $product
     * @param         $field
     * @param         $values
     *
     * $descriptions = array(
     *   'en' => 'what',
     *   'de' => 'was',
     * );
     *
     */
    public function setTranslation(Product $product, $field, $values)
    {
        $product->node->setProperty($field, $values);
        $product->node->setProperty($field.'_local', array_keys($values));
    }


    /**
     * @return Generic
     */
    public function getProductNode()
    {
        if ($this->productNode !== null) {
            return $this->productNode;
        }

        return $this->productNode = $this->dm->find(null, $this->container->getParameter('ecommerce_core.persistence.phpcr.product_basepath'));
    }
}
