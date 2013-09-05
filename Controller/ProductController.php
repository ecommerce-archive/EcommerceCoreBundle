<?php

namespace Ecommerce\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ODM\PHPCR\DocumentManager;

use Ecommerce\Bundle\CoreBundle\Product\Manager as ProductManager;

class ProductController
{
    /** @var ControllerUtils */
    private $utils;

    /** @var ProductManager */
    private $productManager;


    function __construct(ControllerUtils $utils, ProductManager $productManager)
    {
        $this->utils = $utils;
        $this->productManager = $productManager;
    }


    public function indexAction()
    {
        $createdProduct = $this->productManager->createProduct();

        $products = $this->productManager->findAll();

        return $this->render(
        	'EcommerceCoreBundle:Sandbox:index.html.twig',
        	array(
        		'name' => 'there',
        		'products' => $products,
    		)
    	);
    }


    public function newAction()
    {

    }


    public function createAction(Request $request)
    {
        $id = $request->request->get('name');

        $product = $this->productManager->find($id);

        if ($product) {
            throw new \Exception('already exists');
            return $this->redirect($this->generateUrl('ecommerce_core_homepage'));
        }

        $product = $this->productManager->createProduct($id);

        if (!$product) {
            throw new \Exception('Product wasn’t created');
            return $this->redirect($this->generateUrl('ecommerce_core_homepage'));
        }

        $success = $this->productManager->save($product);

        if (!$success) {
            throw new \Exception('New product wasn’t saved');
            return $this->redirect($this->generateUrl('ecommerce_core_homepage'));
        }

        $statusCode = 201;

        $response = new Response();
        $response->setStatusCode($statusCode);

        if (201 === $statusCode) {
            $response->headers->set('Location',
                $this->generateUrl(
                    'ecommerce_product_view', array('id' => $product->getIdentifier()),
                    true
                )
            );
        }

        return $response;
    }


    public function updateAction($id, Request $request)
    {
        $product = $this->productManager->find($id);

        if (!$product) {
            throw $this->createNotFoundException(sprintf('Product with ID %s was not found', $id));
        }

//        $product->setName($request->request->get('name'));

        $product->node->setProperty('cyl', '54da3bec-30f3-4245-80aa-e0d36cd88445', \PHPCR\PropertyType::REFERENCE);

        $success = $this->productManager->save($product);

        if (!$success) {
            throw new \Exception('Product wasn’t saved');
            return $this->redirect($this->generateUrl('ecommerce_core_homepage'));
        }

        $response = new Response(
            '',
            200,
            array(
                'Content-Location' => $this->generateUrl(
                    'ecommerce_product_view', array('id' => $product->getIdentifier()),
                    true
                )
            )
        );

//        $response->headers->set('Content-Location',
//            $this->generateUrl(
//                'ecommerce_product_view', array('id' => $product->getIdentifier()),
//                true
//            )
//        );

        return $response;
    }


    public function viewAction($id)
    {
        $product = $this->productManager->find($id);

        if (!$product) {
            return $this->createNotFoundException(sprintf('Product with ID %s was not found', $id));
        }

        return new Response(var_export($product->getId(), true));
    }


    public function deleteAction($id)
    {
        $product = $this->productManager->find($id);

        if (!$product) {
            return $this->createNotFoundException(sprintf('Product with ID %s was not found', $id));
        }

        $success = $this->productManager->delete($product);

        $response = new Response();
        $response->setStatusCode(204);
        return $response;


        return $this->redirect($this->generateUrl('ecommerce_core_homepage'));
    }
}
