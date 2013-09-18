<?php

namespace Ecommerce\Bundle\CoreBundle\Product;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Document\Generic;

use PHPCR\ItemExistsException;

use Jackalope\Session;
use Jackalope\Factory;
use Jackalope\Node;

use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product;
use Ecommerce\Bundle\CoreBundle\Product\Elastica\Helper as ElasticaHelper;


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
    public function __construct(DocumentManager $dm, $basepath, ElasticaHelper $elasticaHelper)
    {
        $this->dm             = $dm;
        $this->basepath       = $basepath;
        $this->elasticaHelper = $elasticaHelper;
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }


    /**
     * @param bool $name
     * @param bool $withNode
     * @return null|Product
     */
    public function createProduct($name = null, $withNode = false)
    {
        $product = new Product();

        $parentNode = $this->getProductNode();

        if (strlen($name)) {
            if ($parentNode->getNode()->hasNode($name)) {
                return null;
            }
        } elseif ($name === null) {
            $name = sha1(mt_rand());
        }

        $product->setParent($parentNode);
        $product->setNodename($name);
        $product->setName($name);

        if ($withNode) {
            /** @var Session $phpcr */
            $phpcr = $this->dm->getPhpcrSession();
            $productNodeTmp = $phpcr->getNode($this->basepath.'_tmp');
            $node = $productNodeTmp->addNode($name, 'nt:unstructured');

            $product->node = $node;

//            $tmp = new Node(
//                new Factory(),
//                new \stdClass(),
//                $this->getProductNode()->getId().'/'.$product->getNodename(),
//                $this->dm->getPhpcrSession(),
//                $this->dm->getPhpcrSession()->getObjectManager()
//            );
        }

        return $product;
    }


    public function save(Product $product = null)
    {
        try {
            if ($product) {
                $this->dm->persist($product);
            }
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

    public function clearTmp()
    {
        try {
            /** @var Session $phpcr */
            $phpcr = $this->dm->getPhpcrSession();
            $productNodeTmp = $phpcr->getNode($this->basepath.'_tmp');

            $children = $productNodeTmp->getNodes();

            foreach ($children as $child) {
                /** @var Node $child */
                $phpcr->removeItem($child->getPath());
            }

            $phpcr->save();

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
     * @param string $id
     * @return Product|null
     */
    public function findByName($id)
    {
        return $this->dm->find(null, $this->basepath.'/'.$id);
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
     */
    public function populateElasticsearch()
    {
        $this->elasticaHelper->populate();
    }


    /**
     * @param Product $product
     * @param         $field
     * @param         $values
     *
     * $values = array(
     *   'en' => 'Hello',
     *   'de' => 'Hallo',
     *   'fr' => 'Salut',
     * );
     *
     */
    public function setTranslation(Product $product, $field, $values)
    {
        $product->node->setProperty($field, array_values($values));
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

        return $this->productNode = $this->dm->find(null, $this->basepath);
    }
}
