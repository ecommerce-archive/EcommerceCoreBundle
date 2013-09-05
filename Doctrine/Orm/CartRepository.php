<?php

namespace Ecommerce\Bundle\CoreBundle\Doctrine\Orm;

use Doctrine\ORM\EntityRepository;

class CartRepository extends EntityRepository
{
    public function createNewCart()
    {
        $cart = new Cart();

        try {
            $this->_em->persist($cart);
            $this->_em->flush();

            return $cart;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function save(Cart $cart)
    {
        try {
            $this->_em->persist($cart);
            $this->_em->flush();

            return $cart;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function delete(Cart $cart)
    {
        try {
            $this->_em->remove($cart);
            $this->_em->flush();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
