<?php

namespace Ecommerce\Bundle\CoreBundle\Product;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Ecommerce\Bundle\CoreBundle\Product\ProductHandlerInterface;
use Ecommerce\Bundle\CoreBundle\Cart\CartItemValidatorInterface;
// @TODO: Change back to ProductInterface
//use Ecommerce\Bundle\CoreBundle\Model\ProductInterface;
use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product as ProductInterface;

class HandlerManager
{
    private $productHandlers;
    private $eventDispatcher;


    /**
     * Constructor.
     *
     * @param ProductHandlerInterface[] $productHandlers
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $productHandlers)
    {
        if (empty($productHandlers)) {
            throw new \InvalidArgumentException('You must at least add one product handler.');
        }

        $this->productHandlers = $productHandlers;
    }


    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }


    public function getProductHandler(ProductInterface $product)
    {
        $lastException = null;
        $result = null;

        foreach ($this->productHandlers as $productHandler) {
            if (!$productHandler->supports($product)) {
                continue;
            }

            return $productHandler;
        }

        return null;

        // @TODO: Throw exception?
        throw new \Exception(sprintf('No product handler found for product "%s".', $product->getId()));
    }


    public function resolveCartItem(ProductInterface $product, $options)
    {
        $lastException = null;
        $result = null;

        foreach ($this->productHandlers as $productHandler) {
            if (!$productHandler->supports($product)) {
                continue;
            }

            try {
//                $cartHandler = $productHandler->getCartHandler($product);
//                $result = $cartHandler->processRequest($product, $options);
                $result = $productHandler->createCartItem($product, $options);

                if (null !== $result) {
                    break;
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }

        if (null !== $result) {
            if (null !== $this->eventDispatcher) {
                $this->eventDispatcher->dispatch(EcommerceEvents::CART_ITEM_RESOLVED, new CartEvent($result));
            }

            $cartHandler = $productHandler->getCartItemValidator($result);
            if ($cartHandler instanceof CartItemValidatorInterface && $cartHandler->isValid($result) !== true) {
                throw new \Exception('Cart item was not valid');
            }

            return $result;
        }

        if (null === $lastException) {
            $lastException = new \Exception(sprintf('Product with id "%s" could not be resolved to a cart item.', $product->getId()));
        }

//        if (null !== $this->eventDispatcher) {
//            $this->eventDispatcher->dispatch(Events::CARTITEMRESOLVE_FAILURE, new CartItemResolveFailureEvent($product, $options, $lastException));
//        }

//        $lastException->setProduct($product);

        throw $lastException;
    }
}
