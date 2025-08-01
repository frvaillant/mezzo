<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Stock;
use App\Form\ProductType;
use App\Form\StockType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET', 'POST'])]
    public function index(
        ProductRepository $productRepository,
        EntityManagerInterface $manager,
        Request $request
    ): Response {
        $products = $productRepository->findAll();

        $forms = [];

        foreach ($products as $product) {
            $productStock = $product->getStock();

            if(!$productStock) {
                $productStock = new Stock();
                $productStock->setProduct($product)->setQuantity(0);
                $manager->persist($productStock);
                $manager->flush();
            }

            $form = $this->createForm(StockType::class, $productStock, [
                'action' => $this->generateUrl('save_stock', ['stock' => $productStock->getId()])
            ]);

            $form->handleRequest($request);

            $forms[$product->getId()] = $form->createView();

        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'forms' => $forms
        ]);
    }


    #[Route('/savestock/{stock}', name: 'save_stock', methods: ['POST'])]
    public function saveStock( Stock $stock, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
        }

        return $this->redirectToRoute('app_product_index');
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
