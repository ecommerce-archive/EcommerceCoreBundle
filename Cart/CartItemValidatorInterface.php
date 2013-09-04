<?php

namespace Ecommerce\Bundle\CoreBundle\Cart;

use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartItem;

interface CartItemValidatorInterface
{
    public function isValid(CartItem $cartItem);
}
