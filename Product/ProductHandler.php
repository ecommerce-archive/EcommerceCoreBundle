<?php

namespace Ecommerce\Bundle\CoreBundle\Product;

use Ecommerce\Bundle\CoreBundle\Cart\CartItemValidatorInterface;
// @TODO: Change back to ProductInterface
use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product as ProductInterface;

abstract class ProductHandler implements ProductHandlerInterface
{
    private $cartItemValidator;

    private $productAvailabilityChecker;

    private $orderProcessor;

    public function __construct(
        CartItemValidatorInterface $cartItemValidator = null,
        ProductAvailabilityCheckerInterface $productAvailabilityChecker = null,
        OrderProcessorInterface $orderProcessor = null
    ) {
        $this->cartItemValidator          = $cartItemValidator;
        $this->productAvailabilityChecker = $productAvailabilityChecker;
        $this->orderProcessor             = $orderProcessor;
    }

    /**
     * @param ProductInterface $product
     * @return bool
     */
    abstract public function supports(ProductInterface $product);

    /**
     * @return CartItemValidatorInterface|null
     */
    public function getCartItemValidator()
    {
        return null;
    }

    /**
     * @return ProductAvailabilityCheckerInterface|null
     */
    public function getProductAvailabilityChecker()
    {
        return null;
    }

    /**
     * @return OrderProcessorInterface|null
     */
    public function getOrderProcessor()
    {
        return null;
    }
}
