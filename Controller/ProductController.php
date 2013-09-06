<?php

namespace Ecommerce\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\View\View;
use FOS\Rest\Util\Codes;
use JMS\Serializer\SerializationContext;

use Ecommerce\Bundle\CoreBundle\Util\ControllerUtils;
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


    /**
     * @param Request $request
     *
     * @return View
     */
    public function indexAction(Request $request)
    {
        $products = $this->productManager->findAll();

        $view = View::create(
            array(
                 'name' => 'there',
                 'products' => $products,
            )
        );

        $view->setTemplate("EcommerceCoreBundle:Sandbox:index.html.twig");

        $serializationContext = SerializationContext::create();
        $serializationContext->setGroups(
            array_merge(
                array('frontend', 'all'),
                $request->query->has('summary') ? array() : array('cart_full', 'cart_default')
            )
        );
        $view->setSerializationContext($serializationContext);

        return $view;
    }


    /**
     * @param string  $id
     * @param Request $request
     *
     * @return View
     *
     * @throws \Exception
     */
    public function viewAction($id = false, $slug = false, Request $request)
    {
        // @TODO: Create seperate methods
        if ($id) {
            $product = $this->productManager->find($id);
        } else {
            $product = $this->productManager->findByName($slug);
        }

        if (!$product) {
            switch ($request->getRequestFormat()) {
                case 'json':
                    $view = View::create(
                        array('errors' => array(sprintf('Product with id %s not found', $id))),
                        Codes::HTTP_NOT_FOUND
                    );
                    break;

                case 'html':

                    if ($referer = $request->headers->get('Referer')) {
                        $request->getSession()->getFlashBag()->add('error', sprintf('Product with id %s not found', $id));
                        $view = $this->utils->redirectView($referer);
                        break;
                    }

                    throw $this->utils->createNotFoundException('Product not found');
                    $view = $this->utils->routeRedirectView('ecommerce_products', array(), Codes::HTTP_FOUND);
//                    $view = $this->utils->view(null, Codes::HTTP_NOT_FOUND)->setTemplateVar(false);
                    break;

                default:
                    throw new \Exception(sprintf('Unexpected request format "%s"', $request->getRequestFormat()));
            }

            return $view;
        }

        $view = View::create(array('product' => $product));

        switch ($request->getRequestFormat()) {
            case 'json':

                $additionalGroups = array();
                if ($productViews = $request->query->get('view')) {
                    $allowedViews = array('full', 'default');

                    $productViews = explode(',', trim($productViews));
                    foreach ($productViews as $productView) {
                        if (in_array($productView, $allowedViews)) {
                            $additionalGroups = 'product_'.$productView;
                        }
                    }
                }

                $serializationContext = SerializationContext::create();
                $serializationContext->setGroups(
                    array_merge(
                        array('product', 'frontend', 'all'),
                        !empty($additionalGroups) ? $additionalGroups : array('product_default')
                    )
                );
                break;

            case 'html':
                $view->setTemplate("EcommerceCoreBundle:Sandbox:view.html.twig");

                break;

            default:
                throw new \Exception(sprintf('Unexpected request format "%s"', $request->getRequestFormat()));
        }

        return $view;
    }
}
