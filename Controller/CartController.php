<?php

namespace Ecommerce\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;

use Ecommerce\Bundle\CoreBundle\Cart\Manager as CartManager;
use Ecommerce\Bundle\CoreBundle\Product\Manager as ProductManager;
use Ecommerce\Bundle\CoreBundle\Product\HandlerManager;
use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\Cart;
use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\CartItem;
use Ecommerce\Bundle\CoreBundle\Doctrine\Orm\ProductReferenceRepository;

class CartController extends Controller
{
    public function indexAction(Request $request)
    {
        /** @var CartManager $cartManager */
        $cartManager = $this->get('ecommerce_core.cart.manager');

        $cart = $cartManager->getCart();

        // @TODO: Cart items validation

        /** @var ProductManager $productManager */
        $productManager = $this->get('ecommerce_core.product.manager');
        $products = $productManager->findAll();

        return $this->render(
            'EcommerceCoreBundle:Cart:index.html.twig',
            array(
                'cart' => $cart,
                'products' => $products,
            )
        );
    }


    public function addProductAction(Request $request)
    {
        if (!$request->isMethod('POST')) {
            throw new \Exception('Wrong request method');
        }

        if (null === ($productId = $request->request->get('product_id')) || !$productId) {
            throw new \Exception('No product id provided');
        }

        /** @var CartManager $cartManager */
        $cartManager = $this->get('ecommerce_core.cart.manager');

        $cart = $cartManager->getOrCreateCart();

        /** @var ProductManager $productManager */
        $productManager = $this->get('ecommerce_core.product.manager');

        $product = $productManager->find($productId);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        /** @var ProductReferenceRepository $productReferenceRepo */
        $productReferenceRepo = $this->get('ecommerce_core.product_reference.repository');

        $productReference = $productReferenceRepo->getReference($product->getIdentifier());
        if (!$productReference) {
            throw new \Exception('Product reference not found');
        }


        $product->setProductReference($productReference);
//        $productReference->setProduct($productProxy);

        $options = $request->request->get('option');

//        $productTypeManager = $this->get('ecommerce_core.product.type_manager');
        /** @var HandlerManager $productHandlerManager */
        $productHandlerManager = $this->get('ecommerce_core.product.handler_manager');


        // @TODO: event cart pre add item


        try {
            $cartItem = $productHandlerManager->resolveCartItem($productReference, $options);

            if ($cartItem instanceof CartItem) {

                $cartItem->setCart($cart);

                $cartManager->save();
//                $em->persist($cartItem);
//                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'success',
                    sprintf('%s was successfully added to your cart', $cartItem->getProduct()->getName())
                );
            }
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
        }

        // @TODO: event cart post add item

        return $this->redirect($this->generateUrl('ecommerce_cart'));
    }


    // @TODO: updateCartItemAction PUT method


    public function removeCartItemAction(Request $request, $cartItemId)
    {
        if (!$request->isMethod('DELETE')) {
            throw new \Exception('Wrong request method');
        }

//        if (null === ($productId = $request->request->get('product_id'))) {
//            throw new \Exception('No product id provided');
//        }

        /** @var CartManager $cartManager */
        $cartManager = $this->get('ecommerce_core.cart.manager');

        $cart = $cartManager->getOrCreateCart();

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


        return $this->redirect($this->generateUrl('ecommerce_cart'));

        $response = new Response();
        $response->setStatusCode(204);
        return $response;
    }
}
