<?php

namespace Ecommerce\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ProductHandlersPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $productHandlers = new \SplPriorityQueue();
        foreach ($container->findTaggedServiceIds('ecommmerce.product_handler') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $productHandlers->insert($id, $priority);
//            $productHandlers->insert(new Reference($id), $priority);
        }

        $productHandlers = iterator_to_array($productHandlers);
        ksort($productHandlers);

//        $container->getDefinition('ecommerce.product.handler_manager')->replaceArgument(0, array_values($productHandlers));
        $container->setParameter('ecommerce_core.product_handlers', $productHandlers);
    }
}
