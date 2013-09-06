<?php

namespace Ecommerce\Bundle\CoreBundle\Doctrine\Orm;

use Doctrine\ORM\EntityRepository;

class CartItemRepository extends EntityRepository
{
    public function remove(CartItem $cartItem)
    {
        try {
            $this->_em->remove($cartItem);
            $this->_em->flush();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
