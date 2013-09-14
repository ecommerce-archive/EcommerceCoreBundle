<?php

namespace Ecommerce\Bundle\CoreBundle\Product;

use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product;

/**
 * @author Philipp Wahala <philipp.wahala@gmail.com>
 */
interface ProductAvailabilityCheckerInterface
{
    public function isAvailable($productId, array $options = array());
}
