<?php

namespace Ecommerce\Bundle\CoreBundle\Product;

use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartItem;
use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product;

/**
 * @author Philipp Wahala <philipp.wahala@gmail.com>
 */
interface PriceCalculatorInterface
{
    public function calculatePrice(Product $product, CartItem $cartItem);
}
