<?php

namespace Ecommerce\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\View\View;
use FOS\Rest\Util\Codes;
use JMS\Serializer\SerializationContext;

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


    /**
     * Constructor.
     *
     * @param ControllerUtils $utils
     * @param CartManager     $cartManager
     */
    public function __construct(ControllerUtils $utils, CartManager $cartManager)
    {
        $this->utils = $utils;
        $this->cartManager = $cartManager;
    }


    /**
     * @param Request $request
     *
     * @return View
     */
    public function indexAction(Request $request)
    {
        $cart = $this->cartManager->getCart();

        // @TODO: Remove test code
        $products = $this->utils->getProductManager()->findAll(); //->toArray()

        $view = View::create(
            array(
                'cart'     => $cart,
                'products' => $products,
            )
        );

        $view->setTemplate("EcommerceCoreBundle:Cart:index.html.twig");

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
     * @param Request $request
     *
     * @return View
     *
     * @throws \Exception
     */
    public function clearCartAction(Request $request)
    {
        $cart = $this->cartManager->getCart();

        // @TODO: Dispatch event?

        if ($cart) {
            $this->cartManager->delete($cart);
            $cart = null;
        }

        // @TODO: Dispatch event?

        switch ($request->getRequestFormat()) {
            case 'json':
                $view = View::create(null, Codes::HTTP_NO_CONTENT);
                break;

            case 'html':
                // @TODO: Move into event?
                $request->getSession()->getFlashBag()->add('success', 'The cart was successfully cleared');

                if ($referer = $request->headers->get('Referer')) {
                    $view = $this->utils->redirectView($referer);
                    break;
                }
                $view = $this->utils->routeRedirectView('ecommerce_cart', array(), Codes::HTTP_FOUND);
                break;

            default:
                throw new \Exception(sprintf('Unexpected request format "%s"', $request->getRequestFormat()));
        }

        return $view;
    }


    /**
     * @param Request $request
     *
     * @return View
     *
     * @throws \Exception
     */
    public function addProductAction(Request $request)
    {
        $cart = $this->cartManager->getOrCreateCart();

        if (null === ($productId = $request->request->get('product_id'))) {

            switch ($request->getRequestFormat()) {
                case 'json':
                    $view = View::create(array('errors' => array('Required parameter product id missing')), Codes::HTTP_BAD_REQUEST);
                    break;

                case 'html':
                    // @TODO: Move into cart manager event
                    $request->getSession()->getFlashBag()->add('errors', array('Required parameter product id missing'));

                    if ($referer = $request->headers->get('Referer')) {
                        $view = $this->utils->redirectView($referer);
                        break;
                    }
                    $view = $this->utils->routeRedirectView('ecommerce_cart', array(), Codes::HTTP_FOUND);
                    break;

                default:
                    throw new \Exception(sprintf('Unexpected request format "%s"', $request->getRequestFormat()));
            }

            return $view;
        }

        $productManager = $this->utils->getProductManager();

        if (!$productId || !($product = $productManager->find($productId))) {

            $errorMessage = $productId ? sprintf('Product with id %s not found', $productId) : 'No product id provided';

            switch ($request->getRequestFormat()) {
                case 'json':
                    $view = View::create(array('errors' => array($errorMessage)), Codes::HTTP_NOT_FOUND);
                    break;

                case 'html':
                    // @TODO: Move into cart manager event
                    $request->getSession()->getFlashBag()->add('error', $errorMessage);

                    if ($referer = $request->headers->get('Referer')) {
                        $view = $this->utils->redirectView($referer);
                        break;
                    }
                    $view = $this->utils->routeRedirectView('ecommerce_cart', array(), Codes::HTTP_FOUND);
                    break;

                default:
                    throw new \Exception(sprintf('Unexpected request format "%s"', $request->getRequestFormat()));
            }

            return $view;
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
            }
        } catch (\Exception $e) {

            // @TODO: Dispatch event

            switch ($request->getRequestFormat()) {
                case 'json':
                    $view =
                        View::create(
                            array(
                                 'message' => sprintf('Product with id %s could not be added to the cart', $productId),
                                 'errors' => array(
                                     sprintf('Product with id %s could not be added to the cart', $productId) // @TODO: Add errors/options?
                                 )
                            ),
                            Codes::HTTP_UNPROCESSABLE_ENTITY
                        );
                    break;

                case 'html':
                    // @TODO: Move into cart manager event
                    $request->getSession()->getFlashBag()->add('error', $e->getMessage());
//                    $request->getSession()->getFlashBag()->add('error', sprintf('Product with id %s could not be added to the cart', $productId));

                    if ($referer = $request->headers->get('Referer')) {
                        $view = $this->utils->redirectView($referer);
                        break;
                    }
                    $view = $this->utils->routeRedirectView('ecommerce_cart', array(), Codes::HTTP_FOUND);
                    break;

                default:
                    throw new \Exception(sprintf('Unexpected request format "%s"', $request->getRequestFormat()));
            }

            return $view;
        }

        // @TODO: Dispatch event

        switch ($request->getRequestFormat()) {
            case 'json':
//                $view = View::create(array('cart' => $cart), Codes::HTTP_OK);
                $view = View::create(null, Codes::HTTP_CREATED, array());

                $view->setLocation($this->utils->generateUrl('ecommerce_cart_item_view', array('cartItemId' => $cartItem->getId()), true));

                $view->setSerializationContext(SerializationContext::create()->setGroups(
                    array('cart_full', 'cart_default', 'cart', 'frontend', 'all')
                ));

                break;

            case 'html':
                // @TODO: Move into event
                $request->getSession()->getFlashBag()->add('success', sprintf('Product with id %s added to the cart', $productId));

                if ($referer = $request->headers->get('Referer')) {
                    $view = $this->utils->redirectView($referer);
                    break;
                }

//                $view = $this->utils->routeRedirectView('ecommerce_cart', array(), Codes::HTTP_FOUND);
                $view = $this->utils->routeRedirectView('ecommerce_cart_item_view', array(), Codes::HTTP_FOUND);
                break;

            default:
                throw new \Exception(sprintf('Unexpected request format "%s"', $request->getRequestFormat()));
        }

        return $view;
    }


    /**
     * @param Request $request
     * @param string  $cartItemId
     *
     * @return View
     * @throws \Exception
     */
    public function cartItemAction(Request $request, $cartItemId)
    {
        // @TODO: check
        $cart = $this->cartManager->getOrCreateCart();

        $cartItem = $this->cartManager->getCartItem($cart, $cartItemId);

        if (!$cartItem) {
            switch ($request->getRequestFormat()) {
                case 'json':
                    $view = View::create(
                        array('errors' => array(sprintf('Cart item with id %s not found', $cartItemId))),
                        Codes::HTTP_NOT_FOUND
                    );
                    break;

                case 'html':
                    // @TODO: Move into cart manager event
                    $request->getSession()->getFlashBag()->add('error', sprintf('Cart item with id %s not found', $cartItemId));

                    if ($referer = $request->headers->get('Referer')) {
                        $view = $this->utils->redirectView($referer);
                        break;
                    }
                    $view = $this->utils->routeRedirectView('ecommerce_cart', array(), Codes::HTTP_FOUND);
                    break;

                default:
                    throw new \Exception(sprintf('Unexpected request format "%s"', $request->getRequestFormat()));
            }

            return $view;
        }


        switch ($request->getRequestFormat()) {
            case 'json':
                $view = View::create(array('cart_item' => $cartItem));

                $serializationContext = SerializationContext::create();
                $serializationContext->setGroups(
                    array('frontend', 'all')
                );
                $view->setSerializationContext($serializationContext);
                break;

            case 'html':
                $config = false;

                if ($config && ($referer = $request->headers->get('Referer'))) {
                    $view = $this->utils->redirectView($referer);
                    break;
                }

                $view = $this->utils->routeRedirectView('ecommerce_cart', array('item' => $cartItemId), Codes::HTTP_FOUND);
                break;

            default:
                throw new \Exception(sprintf('Unexpected request format "%s"', $request->getRequestFormat()));
        }

        return $view;
    }

    // @TODO: updateCartItemAction PUT method


    /**
     * @param Request $request
     * @param string  $cartItemId
     *
     * @return View
     *
     * @throws \Exception
     */
    public function removeCartItemAction(Request $request, $cartItemId)
    {
        $cart = $this->cartManager->getOrCreateCart();

        $cartItem = $this->cartManager->getCartItem($cart, $cartItemId);

        if (!$cartItem) {
            switch ($request->getRequestFormat()) {
                case 'json':
                    $view = View::create(
                        array('errors' => array(sprintf('Cart item with id %s not found', $cartItemId))),
                        Codes::HTTP_NOT_FOUND
                    );
                    break;

                case 'html':
                    // @TODO: Move into cart manager event
                    $request->getSession()->getFlashBag()->add('error', sprintf('Cart item with id %s not found', $cartItemId));

                    if ($referer = $request->headers->get('Referer')) {
                        $view = $this->utils->redirectView($referer);
                        break;
                    }
                    $view = $this->utils->routeRedirectView('ecommerce_cart', array(), Codes::HTTP_FOUND);
                    break;

                default:
                    throw new \Exception(sprintf('Unexpected request format "%s"', $request->getRequestFormat()));
            }

            return $view;
        }


        $success = $this->cartManager->removeCartItem($cartItem);

        // @TODO: Use $success



        switch ($request->getRequestFormat()) {
            case 'json':
                $view = View::create(null, Codes::HTTP_NO_CONTENT);
                break;

            case 'html':
                // @TODO: Move into event?
                $request->getSession()->getFlashBag()->add(
                    'success',
                    sprintf('%s was successfully removed from your cart', $cartItem->getProduct()->getName())
                );

                if ($referer = $request->headers->get('Referer')) {
                    $view = $this->utils->redirectView($referer);
                    break;
                }
                $view = $this->utils->routeRedirectView('ecommerce_cart', array(), Codes::HTTP_FOUND);
                break;

            default:
                throw new \Exception(sprintf('Unexpected request format "%s"', $request->getRequestFormat()));
        }

        return $view;
    }
}
