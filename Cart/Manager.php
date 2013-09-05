<?php

namespace Ecommerce\Bundle\CoreBundle\Cart;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\Cart;
use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartRepository;

class Manager
{
    /** @var CartRepository */
    private $cartRepository;

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
    public function __construct(CartRepository $cartRepository, Session $session, $storageKey = '_ecommerce_cart_id')
    {
        $this->cartRepository = $cartRepository;
        $this->session        = $session;
        $this->storageKey     = $storageKey;
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

        $cart = $this->cartRepository->createNewCart();

        $this->session->set($this->storageKey, $cart->getId());

        $this->cart = $cart;

        return $cart;
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
