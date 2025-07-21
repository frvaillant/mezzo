<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\PurchaseLineRepository;
use App\Repository\PurchaseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository,
        PurchaseLineRepository $purchaseLineRepository,
        PurchaseRepository $purchaseRepository
    ): Response
    {

        $products = $productRepository->findAll();

        $sellingList = [];
        foreach ($products as $product) {
            $sellingList[] = $product->toArray();
        }

        return $this->render('home/index.html.twig', [
            'products' => $sellingList,
            'account_names' => $purchaseRepository->accountNames(),
            'day_total' => $purchaseLineRepository->todayTotal(),
        ]);
    }
}
