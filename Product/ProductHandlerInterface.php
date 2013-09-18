<?php

namespace Ecommerce\Bundle\CoreBundle\Product;

use Ecommerce\Bundle\CoreBundle\Cart\CartItemValidatorInterface;
// @TODO: Change back to ProductInterface
use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product as ProductInterface;

interface ProductHandlerInterface
{
    /**
     * @param ProductInterface $product
     * @return bool
     */
    public function supports(ProductInterface $product);

    /**
     * @return PriceCalculatorInterface
     */
    public function getPriceCalculator();

    /**
     * @return ProductAvailabilityCheckerInterface|null
     */
    public function getProductAvailabilityChecker();

    /**
     * @return CartItemValidatorInterface|null
     */
    public function getCartItemValidator();

    /**
     * @return OrderProcessorInterface|null
     */
    public function getOrderProcessor();
}
