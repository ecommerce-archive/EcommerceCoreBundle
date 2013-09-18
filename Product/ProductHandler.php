<?php

namespace Ecommerce\Bundle\CoreBundle\Product;

use Ecommerce\Bundle\CoreBundle\Cart\CartItemValidatorInterface;
// @TODO: Change back to ProductInterface
use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product as ProductInterface;

abstract class ProductHandler implements ProductHandlerInterface
{
    private $priceCalculator;

    private $productAvailabilityChecker;

    private $cartItemValidator;

    private $orderProcessor;

    public function __construct(
        PriceCalculatorInterface $priceCalculator,
        ProductAvailabilityCheckerInterface $productAvailabilityChecker = null,
        CartItemValidatorInterface $cartItemValidator = null,
        OrderProcessorInterface $orderProcessor = null
    ) {
        $this->priceCalculator            = $priceCalculator;
        $this->productAvailabilityChecker = $productAvailabilityChecker;
        $this->cartItemValidator          = $cartItemValidator;
        $this->orderProcessor             = $orderProcessor;
    }

    /**
     * @param ProductInterface $product
     * @return bool
     */
    abstract public function supports(ProductInterface $product);

    /**
     * @return PriceCalculatorInterface
     */
    public function getPriceCalculator()
    {
        return $this->priceCalculator;
    }

    /**
     * @return ProductAvailabilityCheckerInterface|null
     */
    public function getProductAvailabilityChecker()
    {
        return $this->productAvailabilityChecker;
    }

    /**
     * @return CartItemValidatorInterface|null
     */
    public function getCartItemValidator()
    {
        return $this->cartItemValidator;
    }

    /**
     * @return OrderProcessorInterface|null
     */
    public function getOrderProcessor()
    {
        return $this->orderProcessor;
    }
}
