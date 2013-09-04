<?php

namespace Ecommerce\Bundle\CoreBundle\Doctrine\Orm;

use Doctrine\ORM\Mapping as ORM;

class CartItem
{
    /** @var string */
    private $id;

    /** @var Cart */
    private $cart;

    /** @var ProductReference */
    private $product;

    /** @var array */
    private $options;

    /** @var float */
    private $price;

    /** @var integer */
    private $sortOrder;

    /** @var \DateTime */
    private $createdAt;

    /** @var \DateTime */
    private $updatedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param Cart $cart
     * @return CartItem
     */
    public function setCart(Cart $cart)
    {
        $lastCartItem = $cart->getItems()->last();
        $this->setSortOrder($lastCartItem instanceof self ? $lastCartItem->getSortOrder() + 1 : 1);

        $cart->addItem($this);

        $this->cart = $cart;

        return $this;
    }


    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }


    /**
     * @ param ProductReference $product
     * @param ProductReference|string $product
     * @return CartItem
     */
//    public function setProduct(ProductReference $product = null)
    public function setProduct($product = null)
    {
        $this->product = $product;

        return $this;
    }


    /**
     * @return ProductReference
     */
    public function getProduct()
    {
        return $this->product;
    }


    /**
     * @param array $options
     * @return CartItem
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }


    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * @param float $price
     * @return CartItem
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }


    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }


    /**
     * @param int $sortOrder
     * @return CartItem
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }


    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }


    /**
     * @param \DateTime|null $createdAt
     * @return CartItem
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt instanceof \DateTime ? $createdAt : new \DateTime();

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }


    /**
     * @param \DateTime|null $updatedAt
     * @return CartItem
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt instanceof \DateTime ? $updatedAt : new \DateTime();

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
