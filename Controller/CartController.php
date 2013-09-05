<?php

namespace Ecommerce\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;

use Ecommerce\Bundle\CoreBundle\Util\ControllerUtils;
use Ecommerce\Bundle\CoreBundle\Cart\Manager as CartManager;
use Ecommerce\Bundle\CoreBundle\Product\HandlerManager;
use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartItem;
use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\ProductReferenceRepository;

class CartController
{
    /** @var ControllerUtils */
    private $utils;

    /** @var CartManager */
    private $cartManager;


    function __construct(ControllerUtils $utils, CartManager $cartManager)
    {
        $this->utils = $utils;
        $this->cartManager = $cartManager;
    }


    public function indexAction(Request $request)
    {
        $view = View::create();

        $view
            ->setTemplate("EcommerceCoreBundle:Cart:index.html.twig")
            ->setTemplateVar('cart')
            ->setSerializationContext(
                SerializationContext::create()
                    ->setGroups(array('cart_index', 'cart', 'frontend', 'all', 'admin'))
                    ->setSerializeNull(true)
            )
        ;


        $cart = $this->cartManager->getCart();

        $data = array('cart' => $cart);

        // @TODO: Cart items availability check

        // @TODO: Remove test code
        $data['products'] = $this->utils->getProductManager()->findAll(); //->toArray()

        $view->setData($data);
        return $view;

        return $this->utils->render(
            'EcommerceCoreBundle:Cart:index.html.twig',
            array(
                'cart' => $cart,
                'products' => $products,
            )
        );
    }


    public function clearCartAction(Request $request)
    {
        if (!$request->isMethod('DELETE')) {
            throw new \Exception('Wrong request method');
        }

        $cart = $this->cartManager->getCart();

        if ($cart) {
            $this->cartManager->delete($cart);
            $cart = null;
        }

        // @TODO: Move into cart manager event
        $request->getSession()->getFlashBag()->add('success', 'The cart was successfully cleared');

        if ($referer = $request->headers->get('Referer')) {
            return $this->utils->redirect($referer);
        }

        return $this->utils->redirect($this->utils->generateUrl('ecommerce_cart'));

        $response = new Response();
        $response->setStatusCode(204);
        return $response;
    }


    public function addProductAction(Request $request)
    {
        if (!$productId = $request->request->get('product_id')) {
            throw new \Exception('No product id provided');
        }

        $cart = $this->cartManager->getOrCreateCart();

        $productManager = $this->utils->getProductManager();

        $product = $productManager->find($productId);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        /** @var ProductReferenceRepository $productReferenceRepo */
        $productReferenceRepo = $this->utils->get('ecommerce_core.product_reference.repository');

        $productReference = $productReferenceRepo->findOrCreate($product);
//        $productReference = $productReferenceRepo->getReference($product->getIdentifier());
        if (!$productReference) {
            throw new \Exception('Product reference not found');
        }

        $product->setProductReference($productReference);

        $options = $request->request->get('option');

//        $productTypeManager = $this->get('ecommerce_core.product.type_manager');
        /** @var HandlerManager $productHandlerManager */
        $productHandlerManager = $this->utils->get('ecommerce_core.product.handler_manager');


        // @TODO: event cart pre add item

        try {
            $cartItem = $productHandlerManager->resolveCartItem($product, $options);

            if ($cartItem instanceof CartItem) {

                $cartItem->setCart($cart);

                $this->cartManager->save();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    sprintf('%s was successfully added to your cart', $cartItem->getProduct()->getName())
                );
            }
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
            if ($targetUrl = $request->headers->get('Referer')) {
                return $this->utils->redirect($targetUrl);
            }
        }

        // @TODO: event cart post add item

        return $this->utils->redirect($this->utils->generateUrl('ecommerce_cart'));
    }


    // @TODO: updateCartItemAction PUT method


    public function removeCartItemAction(Request $request, $cartItemId)
    {
//        if (null === ($productId = $request->request->get('product_id'))) {
//            throw new \Exception('No product id provided');
//        }

        $cart = $this->cartManager->getOrCreateCart();

        $cartItems = $cart->getItems();

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("id", $cartItemId));

        $foundItems = $cartItems->matching($criteria);

        if (count($foundItems) !== 1) {
            // throw exception?
        }

        $cartItem = $foundItems->first();

        // @TODO: event cart pre remove item

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $em->remove($cartItem);
        $em->flush();


        $request->getSession()->getFlashBag()->add(
            'success',
            sprintf('%s was successfully removed from your cart', $cartItem->getProduct()->getName())
        );


        return $this->utils->redirect($this->utils->generateUrl('ecommerce_cart'));

        $response = new Response();
        $response->setStatusCode(204);
        return $response;
    }
}
