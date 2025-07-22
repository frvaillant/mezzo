<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\PurchaseRepository;
use App\Service\DateAmountService;
use App\Service\ReturnableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(

    ): Response
    {


        return $this->render('home/index.html.twig', [

        ]);
    }

    #[Route('/cashbox', name: 'app_cashbox')]
    public function cashbox(
        ProductRepository $productRepository,
        PurchaseRepository $purchaseRepository,
        DateAmountService $amountService,
        ReturnableService $returnableService
    ): Response
    {

        $products = $productRepository->findAll();

        $sellingList = [];
        foreach ($products as $product) {
            $sellingList[] = $product->toArray();
        }

        return $this->render('cashbox/index.html.twig', [
            'products' => $sellingList,
            'account_names' => $purchaseRepository->accountNames(),
            'day_total' => $amountService->getRealDateAmount(new \DateTime()),
            'returnables' => $returnableService->getReturnables()
        ]);
    }
}
