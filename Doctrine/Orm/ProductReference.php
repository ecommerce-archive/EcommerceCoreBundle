<?php

namespace Ecommerce\Bundle\CoreBundle\Doctrine\Orm;

class ProductReference
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    private $product;


    /**
     * Constructor.
     *
     * @param string      $id
     * @param string|null $name
     */
    public function __construct($id, $name = null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /** @return string */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return ProductReference
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /** @return string */
    public function getName()
    {
        return $this->name;
    }

    public function setProduct($proxy)
    {
        $this->product = $proxy;

        return $this;
    }

    public function getProduct()
    {
        return $this->product;
    }


}
