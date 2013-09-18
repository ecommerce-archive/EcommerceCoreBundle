<?php

namespace Ecommerce\Bundle\CoreBundle\Product\Elastica;

use FOS\ElasticaBundle\IndexManager;
use FOS\ElasticaBundle\Provider\ProviderRegistry;
use FOS\ElasticaBundle\Resetter;

class Helper
{
    private $indexManager;

    private $providerRegistry;

    private $resetter;

    /**
     * Constructor.
     */
    public function __construct(IndexManager $indexManager, ProviderRegistry $providerRegistry, Resetter $resetter)
    {
        $this->indexManager     = $indexManager;
        $this->providerRegistry = $providerRegistry;
        $this->resetter         = $resetter;
    }

    public function populate()
    {
        $indexes = array_keys($this->indexManager->getAllIndexes());

        foreach ($indexes as $index) {
            $this->resetter->resetIndex($index);

            $providers = $this->providerRegistry->getIndexProviders($index);

            foreach ($providers as $provider) {
                $provider->populate();
            }

            $this->indexManager->getIndex($index)->refresh();
        }

//        $this->resetter();
    }

    public function resetter()
    {
        exit('wtf');
        $resetter = new \FOS\ElasticaBundle\Resetter(array(
            'glamourrent' => array(
                'index'  => $this->indexManager->getIndex('glamourrent'),
//                'index'  => $this->get(
//                    'fos_elastica.index.glamourrent'
//                ),
                'config' => array(
                    'mappings' => array(
                        'products' => array(
                            'properties' => array(
                                'name'       => array(
                                    'type'           => 'string',
                                    'include_in_all' => true
                                ),
                                'description'  => array(
                                    'type'           => 'nested',
//                                    'include_in_all' => true
                                ),
                                'createdAt'  => array(
                                    'type'           => 'string',
                                    'include_in_all' => true
                                ),
                                'modifiedAt' => array(
                                    'type'           => 'string',
                                    'include_in_all' => true
                                )
                            )
                        )
                    )
                )
            )
        ));

        $resetter->resetIndexType('glamourrent', 'products');
//        $resetter->resetIndex('glamourrent');
    }
}
