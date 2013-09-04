<?php

namespace Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr;

use Doctrine\ODM\PHPCR\Document\Generic;

use Jackalope\Node;

use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\ProductReference;

class Product
{
    const STATUS_CREATED = 0;
    const STATUS_DRAFT = 1;
    const STATUS_OPEN = 2;
    const STATUS_CHECKOUT = 3;

    private $id;

    protected $nodename;

    /** @var Generic */
    protected $parent;

    /** @var string */
    protected $locale;

    /** @var string */
    protected $name;

    /** @var integer */
    protected $status;

    /** @var Node */
    public $node;

    /** @var ProductReference */
    private $productReference;


    public function __construct($locale = 'en')
    {
        $this->locale = $locale;

        $this->status = self::STATUS_CREATED;
    }



    public function getId()
    {
        return $this->id;
    }

    public function getIdentifier()
    {
        return $this->node->getIdentifier();
    }


    /**
     * @return string
     */
    public function getNodename()
    {
        return $this->nodename;
    }

    /**
     * @param string $name the name of the document
     * @return Product
     */
    public function setNodename($name)
    {
        $this->nodename = $name;

        return $this;
    }


    public function getParent()
    {
        return $this->parent;
    }


    /**
     * @param mixed $parent
     * @return Product
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }


    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }


    /**
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }



    public function getNode()
    {
        return $this->node;
    }

    public function all()
    {
        return $this->node->getPropertiesValues();
    }


    /**
     * @param $name
     * @return array|mixed|\PHPCR\NodeInterface|resource
     */
    public function get($name)
    {
        return $this->node->getPropertyValue($name);
    }


    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return $this->node->hasProperty($name);
    }


    /**
     * @param string $name
     * @param mixed  $value
     * @return Product
     */
    public function set($name, $value)
    {
        $this->node->setProperty($name, $value);

        return $this;
    }

    public function getIterator()
    {
        return $this->node->getProperties();
    }


    public function setProductReference($proxy)
    {
        $this->productReference = $proxy;

        return $this;
    }

    public function getProductReference()
    {
        return $this->productReference;
    }


    /*
    public function __toString()
    {
        return (string) $this->nodename;
    }
    */
}
