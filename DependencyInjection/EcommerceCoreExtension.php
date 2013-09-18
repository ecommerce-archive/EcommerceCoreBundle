<?php

namespace Ecommerce\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class EcommerceCoreExtension extends Extension implements PrependExtensionInterface
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

    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (!isset($bundles['FOSElasticaBundle'])) {

            $config = array('use_acme_goodbye' => false);
            foreach ($container->getExtensions() as $name => $extension) {
                switch ($name) {
                    case 'acme_something':
                    case 'acme_other':
                        // set use_acme_goodbye to false in the config of acme_something and acme_other
                        // note that if the user manually configured use_acme_goodbye to true in the
                        // app/config/config.yml then the setting would in the end be true and not false
                        $container->prependExtensionConfig($name, $config);
                        break;
                }
            }
        }


        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);
        $config['es_index'] = 'glamourrent';

        if (isset($config['es_index'])) {
            // prepend the acme_something settings with the entity_manager_name
//            $config = array('entity_manager_name' => $config['entity_manager_name']);
            $config = array(
                'indexes' => array(
                    $config['es_index'] => array(
                        'types' => array(
                            'products' => array(
                                'mappings' => array(
                                    'random' => array(
                                        'type' => 'string'
                                    )
                                )
                            )
                        )
                    )
                )
            );
            $container->prependExtensionConfig('fos_elastica', $config);
        }

    }
}
