<?php

namespace Ecommerce\Bundle\CoreBundle\Product;

use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartItem;
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
     * @param ProductInterface $product
     * @return CartHandlerInterface
     */
    public function getCartHandler(ProductInterface $product);

    /**
     * @param CartItem $cartItem
     * @return CartItemValidatorInterface
     */
    public function getCartItemValidator(CartItem $cartItem);
}
