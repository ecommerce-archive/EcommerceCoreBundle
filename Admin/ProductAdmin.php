<?php

namespace Ecommerce\Bundle\CoreBundle\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormBuilder;

use Sonata\DoctrinePHPCRAdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;


use Ecommerce\Bundle\CoreBundle\Product\Manager as ProductManager;
use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product;
use Ecommerce\Bundle\CoreBundle\Product\Form\DataMapper\ProductDataMapper;


class ProductAdmin extends Admin
{
    /** @var ProductManager */
    private $productManager;

    /**
     * @param string         $code
     * @param string         $class
     * @param string         $baseControllerName
     * @param ProductManager $productManager
     */
    public function __construct($code, $class, $baseControllerName, ProductManager $productManager)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->productManager = $productManager;


    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        if (!$this->getSubject()->getId()) {
            $formMapper
                ->with('General information')
                    ->add('name', 'text', array('label' => 'Name'))
                ->end()
            ;
            return;
        }
    }


    public function defineFormBuilder(FormBuilder $formBuilder)
    {
        $formBuilder->setDataMapper(new ProductDataMapper());

        parent::defineFormBuilder($formBuilder);
    }


    //Fields to be shown on filter forms


    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
//            ->add('createdAt')//            ->add('author')
        ;
    }


    //Fields to be shown on lists


    protected function configureListFields(ListMapper $listMapper)
    {

        $productField = new ProductFieldDescription();
        $productField->setName('Product type');
        $productField->setFieldName('product_type');

        $listMapper
            ->addIdentifier('id')
            ->add($productField)
//            ->add(
//                'product_type',
//                'text',
//                array(
//                     'route'      => array('name' => 'show', 'parameters' => array()),
//                     'identifier' => 'product_type',
//                     'code' => 'get("product_type")',
//                )
//            )
            ->add('identifier', 'text');
    }

    public function getNewInstance()
    {
        $new = false;

        if ($this->hasRequest()) {
            $request = $this->getRequest();
            if ($request->request->has($this->getUniqid()) && ($formData = $request->request->get($this->getUniqid()))) {
                //$new->setNodename($formData['name']);
                $new  = $this->productManager->createProduct($formData['name'], true);

                if (((Request::getHttpMethodParameterOverride() || !$request->request->has('_method'))
                    && $request->getMethod() == 'POST')
                    || $request->request->get('_method') == 'POST'
                ) {
                    // $this->getModelManager()->create($new);
                }
            }
        }

        if (!$new) {
            $new  = $this->productManager->createProduct(null, true);
        }

        return $new;


        /** @var Product $new */
        $new = parent::getNewInstance();

        if ($this->hasRequest()) {
            $request = $this->getRequest();

            $parentId = $request->query->get('parent');
            if (null !== $parentId) {
                $new->setParent($this->getModelManager()->find(null, $this->getRootPath()));
            }
            $new->setParent($this->getModelManager()->find(null, $this->getRootPath()));


        }

        return $new;
    }


    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $this->productManager->clearTmp();
    }


    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
        $this->productManager->clearTmp();
        $this->productManager->populateElasticsearch();
    }


    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
        $this->productManager->populateElasticsearch();
    }
}
