<?php

namespace Ecommerce\Bundle\CoreBundle\Doctrine\Orm;

use Doctrine\ORM\EntityRepository;

use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product;

class ProductReferenceRepository extends EntityRepository
{
    public function create(Product $product)
    {
        $productReference = new ProductReference($product->getIdentifier(), $product->getName());

        $this->_em->persist($productReference);
        $this->_em->flush();

        return $productReference;
    }

    public function getReference(Product $product)
    {
        $productReference = $this->find($product->getIdentifier());

        // @TODO: Log this - shouldn’t happen!!
        if (!$productReference) {
            $productReference = $this->create($product);
        }

        return $productReference;
    }

    public function delete(Product $product)
    {
        $productReference = $this->find($product->getIdentifier());

        if (!$productReference) {
            return false;
        }


        $this->_em->remove($productReference);
        $this->_em->flush();

        return true;
    }
}
