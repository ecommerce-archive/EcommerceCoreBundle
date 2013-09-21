<?php

namespace Ecommerce\Bundle\CoreBundle\Product\Elastica;

use FOS\ElasticaBundle\Provider\ProviderInterface;

use Elastica\Type;
use Elastica\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Util\ClassUtils;

use Ecommerce\Bundle\CoreBundle\Product\Manager as ProductManager;
use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\ProductReference;
use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product;

class Provider implements ProviderInterface
{
    /** @var ProductManager */
    private $productManager;

    protected $productType;

//    protected $categoryRepository;

//    protected $designerRepository;

//    /** @var  ArrayCollection */
//    protected $categories;

//    /** @var  ArrayCollection */
//    protected $designers;


    /**
     * Constructor.
     */
    public function __construct(Type $productType, ProductManager $productManager) // , CategoryRepository $categoryRepository, DesignerRepository $designerRepository)
    {
        $this->productType        = $productType;
        $this->productManager     = $productManager;
//        $this->categoryRepository = $categoryRepository;
//        $this->designerRepository = $designerRepository;
    }


    /**
     * @param \Closure $loggerClosure
     * @return void
     */
    public function populate(\Closure $loggerClosure = null)
    {
        if ($loggerClosure) {
            $loggerClosure('Indexing products');
        }

        $products = $this->productManager->findAll();

        if (empty($products)) {
            return;
        }

        $documents = array();

        foreach ($products as $product) {
            $documents[] = $this->transformToDocument($product);
        }

        $this->productType->addDocuments($documents);
    }

    public function insertDocument(Product $product)
    {
        $document = $this->transformToDocument($product);
        $this->productType->addDocument($document);
    }

    public function updateDocument(Product $product)
    {
        $document = $this->transformToDocument($product);
        $this->productType->updateDocument($document);
    }




    public function transformToDocument(Product $product)
    {
        $data = array(
            'id'   => $product->getIdentifier(),
            'name' => $product->getName(),
        );

//        $data['images']            = array(
//            'main'   => 'http://gr.dev/img/layout/glamourrent_logo_header.png',
//        );

        $ignoredProperties = array(
            'jcr:primaryType',
            'jcr:mixinTypes',
            'phpcr:class',
            'phpcr:classparents',
            'jcr:uuid',
        );
//        $productData = array_diff_key($product->getPublicNodeProperties(), array_flip($ignoredProperties));

        $productData = $product->getTranslatedProperties();

        $data = array_merge($data, $productData);

        if (ClassUtils::getClass($product) === 'Ecommerce\Bundle\CoreBundle\Doctrine\Orm\ProductReference') {

        }

//        $time_start = microtime(true);
//        $data = array_merge($data, $this->getAssociatedData($product));
//        $time = microtime(true) - $time_start;
//        echo sprintf('%06f', $time)."\n";

        $availability = array();

        for ($i = 1; $i <= 90; $i++) {
            if (!rand(0, 2)) {
                $date = new \DateTime('+'.$i.'days');
                $availability[] = $date->format('Y-m-d');
            }
        }

        $data['availability'] = $availability;

        $data = $this->filterValues($data);


        $data['last_synced_at'] = date('c');

        $document = new Document($product->getId(), $data);

        return $document;
    }

    private function filterValues($data)
    {
        $renamedProperties = array(
            'created_by' => 'jcr:createdBy',
            'created_at' => 'jcr:created',
            'updated_at' => 'jcr:lastModifiedBy',
            'updated_by' => 'jcr:lastModified',
        );

        $filteredData = array();
        foreach ($data as $property => $value) {

            if (is_array($value) && !empty($value) && array_keys($value) !== range(0, count($value) - 1)) {

                if ($throwLocaleAway = false) {
                    $value = array_values($value);
                } elseif ($mappingForTranslatedValues = true) {
                    $nestedArray = array();
                    foreach ($value as $locale => $translation) {
                        $nestedArray[] = array(
                            'locale' => $locale,
                            'value' => $translation,
                        );
                    }
                    $value = $nestedArray;
                } else {
                    $nestedArray = array();
                    foreach ($value as $locale => $translation) {
                        $nestedArray[] = array(
                            $locale,
                            $translation,
                        );
                    }
                    $value = $nestedArray;
                }
            }

            if ($value instanceof \DateTime) {
                $value = $value->format('c');
            }

            if (($newPropertyKey = array_search($property, $renamedProperties)) !== false) {
                $property = $newPropertyKey;
            }

            $filteredData[$property] = $value;
        }

        return $filteredData;
    }

    private function filterValues2($data)
    {
        foreach ($data as $property => $value) {

            if (is_array($value)) {

                // skip non associative arrays
                if (empty($value) || array_keys($value) === range(0, count($value) - 1)) {
                    continue;
                }

                if ($throwLocaleAway = false) {
                    $data[$property] = array_values($value);
                } elseif ($mappingForTranslatedValues = true) {
                    $nestedArray = array();
                    foreach ($value as $locale => $translation) {
                        $nestedArray[] = array(
                            'locale' => $locale,
                            'value' => $translation,
                        );
                    }
                    $data[$property] = $nestedArray;
                } else {
                    $nestedArray = array();
                    foreach ($value as $locale => $translation) {
                        $nestedArray[] = array(
                            $locale,
                            $translation,
                        );
                    }
                    $data[$property] = $nestedArray;
                }
                continue;
            }

            if ($value instanceof \DateTime) {
                $data[$property] = $value->format('c');
                continue;
            }
        }

        return $data;
    }

    private function getAssociatedData($product)
    {
        $associatedData = array();

        $associatedData = array_merge($associatedData, $this->getCategory($product));
        $associatedData = array_merge($associatedData, $this->getDesigner($product));

        return $associatedData;
    }


    private function getCategory($product)
    {
        $category = $this->getCategoryForProduct($product);
        if (!$category) {
            return array();
        }

        return array('category' => $category->getId());
    }

    private function getCategoryForProduct($product)
    {
        if (!$this->categories instanceof ArrayCollection) {
            $this->categories = new ArrayCollection($this->categoryRepository->getAllWithProducts());
        }

        foreach ($this->categories->toArray() as $category) {
            /** @var Category $category */
            foreach ($category->getMappedProducts() as $mappedProduct) {
                if ($mappedProduct->getProduct()->getId() === $product->getId()) {
                    return $category;
                }
            }
        }

        return false;
    }


    private function getDesigner($product)
    {
        $designer = $this->getDesignerForProduct($product);
        if (!$designer) {
            return array();
        }

        return array('designer' => $designer->getId());
    }

    private function getDesignerForProduct($product)
    {
        if (!$this->designers instanceof ArrayCollection) {
            $this->designers = new ArrayCollection($this->designerRepository->getAllWithProducts());
        }

        foreach ($this->designers->toArray() as $designer) {
            /** @var Designer $designer */
            foreach ($designer->getMappedProducts() as $mappedProduct) {
                if ($mappedProduct->getProduct()->getId() === $product->getId()) {
                    return $designer;
                }
            }
        }

        return false;
    }
}
