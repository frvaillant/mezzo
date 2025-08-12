<?php

namespace App\Controller;

use App\Repository\PurchaseLineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CashRegisterController extends AbstractController
{
    #[Route('/till/{year}', name: 'app_cash_register')]
    public function index(PurchaseLineRepository $purchaseLineRepository, ?string $year = null): Response
    {
        $year = $year ?? date('Y');

        return $this->render('cash_register/index.html.twig', [
            'year' => $year,
            'season_total'  => $purchaseLineRepository->getTotalSoldBySeason($year),
            'season_detail' => $purchaseLineRepository->getTotalSoldByDay($year),
        ]);
    }
}
