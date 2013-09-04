<?php

namespace Ecommerce\Bundle\CoreBundle\Doctrine\Orm;

use Doctrine\ORM\EntityRepository;

class CartRepository extends EntityRepository
{
    public function createNewCart()
    {
        $cart = new Cart();

        $this->_em->persist($cart);
        $this->_em->flush();

        return $cart;
    }

    public function save(Cart $cart)
    {
        $this->_em->persist($cart);
        $this->_em->flush();

        return $cart;
    }
}
