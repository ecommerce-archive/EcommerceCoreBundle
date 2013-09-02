<?php

namespace Ecommerce\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class EcommerceCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->loadPhpcr($config['persistence']['phpcr'], $loader, $container);


    }

    public function loadPhpcr($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $keys = array(
            'product_basepath' => 'product_basepath',
            'manager_name' => 'manager_name',
        );

        foreach ($keys as $sourceKey => $targetKey) {
            if (isset($config[$sourceKey])) {
                $container->setParameter(
                    $this->getAlias() . '.persistence.phpcr.'. $targetKey,
                    $config[$sourceKey]
                );
            }
        }


        // @TODO: Define service
//        $loader->load('persistence-phpcr.xml');

//        $productManager = $container->getDefinition('ecommerce.product.manager');
//        $productManager->addMethodCall('setManagerName', array('%ecommerce_core.persistence.phpcr.manager_name%'));
    }
}
