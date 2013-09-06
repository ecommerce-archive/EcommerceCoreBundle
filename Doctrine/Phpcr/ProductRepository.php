<?php

namespace Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr;

use Doctrine\ODM\PHPCR\DocumentRepository;

class ProductRepository extends DocumentRepository
{
    /**
     * @param string $productId
     * @return Product
     */
    public function getReference($productId)
    {
        $productReference = $this->dm->getReference($this->getClassName(), $productId);

        return $productReference;
    }
}
