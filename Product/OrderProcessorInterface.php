<?php

namespace Ecommerce\Bundle\CoreBundle\Product;

/**
 * @author Philipp Wahala <philipp.wahala@gmail.com>
 */
interface OrderProcessorInterface
{
    public function processOrder($order, $orderItem);
}
