<?php

namespace Ecommerce\Bundle\CoreBundle\Cart;

interface CartHandlerInterface
{
    public function processRequest(ProductInterface $product, $options);
}
