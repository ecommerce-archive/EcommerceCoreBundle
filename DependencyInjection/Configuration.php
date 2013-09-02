<?php

namespace Ecommerce\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ecommerce_core');

        $rootNode
            ->children()
                ->arrayNode('persistence')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('phpcr')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('product_basepath')->defaultValue('/ecommerce/product')->end()
                                ->scalarNode('manager_name')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
