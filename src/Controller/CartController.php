<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\Cart\CartService;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart_index")
     */
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/cart.html.twig', [
            'items' => $cartService->getFullCartWithoutSerialize(),
            'total' => $cartService->getTotal(),
            'shipPrice' => $cartService->getShipPrice()
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="cart_add")
     */
    public function add($id, CartService $cartService)
    {
        $cartService->add($id);
        $items = $cartService->getFullCartWithoutSerialize();
        $subTotal = $cartService->getSubTotal();
        $total = $cartService->getTotal();
        $count = $cartService->getCount();
        $shipPrice = $cartService->getShipPrice();

        return new JsonResponse(['display' => $this->renderView("home/shopping-item.html.twig", ['items' => $items, 'subTotal' => $subTotal, 'total' => $total, 'shipPrice' => $shipPrice, 'count' => $count])]);
    }

    /**
     * @Route("/cart/minus/{id}", name="cart_minus")
     */
    public function minus($id, CartService $cartService)
    {
        $cartService->minus($id);
        $items = $cartService->getFullCartWithoutSerialize();
        $total = $cartService->getTotal();
        $count = $cartService->getCount();

        return new JsonResponse(['display' => $this->renderView("home/shopping-item.html.twig", ['items' => $items, 'total' => $total, 'count' => $count])]);
    }

    /**
     * @Route("/cart/ajax", name="cart_ajax")
     */
    public function display(CartService $cartService)
    {
        $items = $cartService->getFullCartWithoutSerialize();
        $subTotal = $cartService->getSubTotal();
        $total = $cartService->getTotal();
        $count = $cartService->getCount();
        $shipPrice = $cartService->getShipPrice();

        return new JsonResponse(['display' => $this->renderView("home/shopping-item.html.twig", ['items' => $items, 'subTotal' => $subTotal, 'total' => $total, 'shipPrice' => $shipPrice, 'count' => $count])]);
    }

    /**
     * @Route("/cart/remove/{id}", name="cart_remove")
     */
    public function remove($id, CartService $cartService): Response
    {
        $cartService->remove($id);
        $total = $cartService->getTotal();

        return new JsonResponse($total);
    }
}
