<?php

namespace Ecommerce\Bundle\CoreBundle\Cart;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Doctrine\Common\Collections\Criteria;

use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\Cart;
use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartRepository;
use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartItem;
use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartItemRepository;

class Manager
{
    /** @var CartRepository */
    private $cartRepository;

    /** @var CartItemRepository */
    private $cartItemRepository;

    /** @var Session */
    private $session;

    /** @var string */
    private $storageKey;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var Cart */
    private $cart;



    /**
     * Constructor.
     */
    public function __construct(CartRepository $cartRepository, CartItemRepository $cartItemRepository, Session $session, $storageKey = '_ecommerce_cart_id')
    {
        $this->cartRepository     = $cartRepository;
        $this->cartItemRepository = $cartItemRepository;
        $this->session            = $session;
        $this->storageKey         = $storageKey;
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }


    /**
     * Get the current cart
     *
     * @return Cart|null
     */
    public function getCart()
    {
        if ($this->cart !== null) {
            return $this->cart;
        }

        if (null !== ($cartId = $this->getCartIdFromSession())) {
            $cart = $this->cartRepository->find($cartId);

            if ($cart && $cart->isExpired()) {
                $cart = null;
            }

            // @TODO: Cart items availability check

            if ($cart) {
                $this->cart = $cart;
            }

            return $cart;
        }

        return null;
    }


    /**
     * Get the current cart or create a new one
     *
     * @return Cart
     */
    public function getOrCreateCart()
    {
        if (null !== ($cart = $this->getCart())) {
            return $cart;
        }

        return $this->createCart();
    }


    /**
     * Create a new cart
     *
     * @return Cart
     */
    public function createCart()
    {
        $cart = $this->cartRepository->createNewCart();

        $this->session->set($this->storageKey, $cart->getId());

        $this->cart = $cart;

        return $cart;
    }


    /**
     * Get a cart item from a cart
     *
     * @return Cart
     */
    public function getCartItem(Cart $cart, $cartItemId)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("id", $cartItemId));

        $foundItems = $cart->getItems()->matching($criteria);

        return $foundItems->first();
    }


    /**
     * Get a cart item from a cart
     *
     * @return Cart
     */
    public function removeCartItem(CartItem $cartItem)
    {
        return $this->cartItemRepository->remove($cartItem);
    }

    /**
     * Save the current cart
     *
     * @throws \Exception
     * @return Cart
     */
    public function save()
    {
        if (null === ($cart = $this->getCart())) {
            throw new \Exception('You can only save the cart after loading/creating it');
        }

        return $this->cartRepository->save($cart);
    }


    /**
     * Delete a cart
     *
     * @param Cart $cart
     * @return bool
     */
    public function delete(Cart $cart)
    {
        return $this->cartRepository->delete($cart);
    }


    /**
     * Retrieve cart id from session
     *
     * @return string|null
     */
    private function getCartIdFromSession()
    {
        return $this->session->get($this->storageKey);
    }
}
