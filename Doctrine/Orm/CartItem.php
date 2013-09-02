<?php

namespace Ecommerce\Bundle\CoreBundle\Doctrine\Orm;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartItemRepository")
 * @ORM\Table(name="ecommerce_cart_item")
 */
class CartItem
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var Cart
     *
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="items")
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id", nullable=false)
     */
    private $cart;

    /**
     * @var ProductReference
     *
     * @ORM\ManyToOne(targetEntity="Ecommerce\Bundle\CoreBundle\Doctrine\Orm\ProductReference")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true)
     */
    private $product;

    /**
     * @var array
     *
     * @ORM\Column(name="options", type="array")
     */
    private $options;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="decimal", precision=6, scale=2, nullable=true)
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(name="sort_order", type="smallint", nullable=true)
     */
    private $sortOrder;


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
     * @param ProductReference $product
     * @return CartItem
     */
    public function setProduct(Product $product = null)
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
}
