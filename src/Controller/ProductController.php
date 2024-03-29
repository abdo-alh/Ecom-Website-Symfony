<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Review;
use App\Form\ReviewForm;
use App\Form\SearchForm;
use App\Model\SearchData;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    /**
     * @Route("/product", name="product")
     */
    public function index(ProductRepository $productRepo): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepo->findSomeProducts('category', 'Electronics', 4),
        ]);
    }

    /**
     * @Route("/shop", name="shop")
     */
    public function shop(ProductRepository $productRepo, Request $request): Response
    {
        $data = new SearchData();
        $data->page = $request->get('page', 1);
        [$min, $max] = $productRepo->findMinMax($data);
        $form = $this->createForm(SearchForm::class, $data);
        $form->handleRequest($request);
        $products = $productRepo->findSearch($data);
        if ($request->get('ajax')) {
            return new JsonResponse([
                'sorting' => $this->renderView('product/_sorting.html.twig', ['products' => $products]),
                'content' => $this->renderView('product/_products.html.twig', ['products' => $products]),
                'pagination' => $this->renderView('product/_pagination.html.twig', ['products' => $products]),
                //'pages' => ceil($products->getTotalItemCount() / $products->getItemNumberPerPage()),
                'min' => $min,
                'max' => $max
            ]);
        }
        return $this->render('product/shop-grid.html.twig', [
            'products' => $products,
            'form' => $form->createView(),
            'min' => $min,
            'max' => $max
        ]);
    }

    /**
     * @Route("/productModal/{id}", name="productModal")
     */
    public function loadModal($id, ProductRepository $productRepo)
    {
        $product = $productRepo->find($id);
        return $this->render('product/productModal.html.twig', ['product' => $product]);
    }

    /**
     * @Route("/product/{id}", name="product")
     */
    public function showProduct(Product $product, ProductRepository $productRepo,Request $request)
    {
        $products = $productRepo->findSomeProducts('category', $product->getCategory()->getName(), 3);
        $review = new Review();
        $form = $this->createForm(ReviewForm::class, $review);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $review->setProduct($product);
            $review->setCreatedAt(new \DateTime("now"));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('message', 'Review Added with Success !');
            return $this->redirectToRoute('product',['id' => $product->getId()]);
        }
        return $this->render('product/product-page.html.twig', ['product' => $product, 'products' => $products,'form' => $form->createView(),'reviews'=>$product->getReviews()]);
    }
}
