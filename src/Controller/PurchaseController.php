<?php

namespace App\Controller;

use App\Dto\PurchaseProductDto;
use App\Entity\Purchase;
use App\Entity\PurchaseLine;
use App\Entity\ReturnableGlassReturn;
use App\Repository\ProductRepository;
use App\Repository\PurchaseLineRepository;
use App\Repository\PurchaseRepository;
use App\Repository\ReturnableGlassReturnRepository;
use App\Service\DateAmountService;
use App\Service\ReturnableService;
use App\Service\StockManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PurchaseController extends AbstractController
{


    /**
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $manager
     * @param string|null $account
     * @return JsonResponse
     *
     * Enregistre une vente, réelle ou en compte/ardoise
     */
    #[Route('/purchase/{account}', name: 'purchase', methods: ['POST'])]
    public function purchase(
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $manager,
        StockManager $stockManager,
        string $account = null
    )
    {
        $data = json_decode($request->getContent(), true);

        if(!empty($data)) {

            try {

                $purchase = new Purchase();
                $purchase->setPaymentMode($data['mode']);
                $purchase->setAccount($account);
                $manager->persist($purchase);

                foreach ($data['purchase'] as $productId => $cart) {

                    $cart = new PurchaseProductDto($cart);

                    $product = $productRepository->find($productId);

                    $stockManager->decreaseStock($product, $cart->quantity);

                    if ($product) {
                        $line = new PurchaseLine();
                        $consigneAmount = $cart->total - $cart->totalProduct;
                        $line
                            ->setProduct($product)
                            ->setQuantity($cart->quantity)
                            ->setUnitPrice($product->getUnitPrice())
                            ->setTotal($cart->totalProduct)
                            ->setConsigne($consigneAmount);
                        $manager->persist($line);
                        $purchase->addLine($line);

                    }

                }

                $manager->flush();

                return new JsonResponse([], Response::HTTP_OK);

            } catch (\Exception $e) {
                return new JsonResponse([], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);

    }


    /**
     * @param PurchaseLineRepository $purchaseLineRepository
     * @return JsonResponse
     *
     * Retourne le total des ventes du jour
     */
    #[Route('/daytotal/{date}', name: 'day_total', methods: ['POST'])]
    public function getDayTotal(
        DateAmountService $dateAmountService,
        ?string $date = null
    ): JsonResponse
    {
        $date = $date ? new \DateTime($date) : new \DateTime();

        return new JsonResponse(['total' => $dateAmountService->getRealDateAmount($date)], Response::HTTP_OK);
    }


    /**
     * @param PurchaseRepository $purchaseRepository
     * @param PurchaseLineRepository $purchaseLineRepository
     * @param string|null $date
     * @return Response
     * @throws \Exception
     *
     * Affiche la liste des paiements du jour
     */
    #[Route('/caisse-du-jour/{date}', name: 'purchase_list')]
    public function dayList(
        PurchaseRepository $purchaseRepository,
        PurchaseLineRepository $purchaseLineRepository,
        DateAmountService $dateAmountService,
        string $date = null
    ) {

        $date = $date ? new \DateTime($date) : new \DateTime();

        $list = $purchaseRepository->dayList($date);

        $total = $purchaseLineRepository->todayTotal($date);

        $accounts = $purchaseLineRepository->getDailyTotalByAccount($date);

        $returnables = $purchaseLineRepository->getDailyReturnables($date);
        $returns = $dateAmountService->getReturnsCount($date);

        $unReturned = $returnables - $returns;

        $paymentModesTotal = $purchaseLineRepository->todayTotalByPaymentMode($date);

        $quantityByProduct = $purchaseLineRepository->todayTotalSoldByProduct($date);

        $quantityByProduct[] = [
            'quantity' => $unReturned,
            'name' => 'Consignes non rendues',
            'unitPrice' => 1
        ];

        return $this->render('purchase/list.html.twig', [
            'date' => $date,
            'day_total' => $total,
            'list' => $list,
            'accounts' => $accounts,
            'returns' => $returns,
            'payment_modes' => $paymentModesTotal,
            'products_quantities' => $quantityByProduct
        ]);

    }


    #[Route('/resume/{date}', name: 'purchase_list_resume')]
    public function resume(
        PurchaseRepository $purchaseRepository,
        PurchaseLineRepository $purchaseLineRepository,
        DateAmountService $dateAmountService,
        string $date = null
    ) {

        $date = $date ? new \DateTime($date) : new \DateTime();

        $list = $purchaseRepository->dayList($date);

        $total = $purchaseLineRepository->todayTotal($date);

        $accounts = $purchaseLineRepository->getDailyTotalByAccount($date);

        $returns  = $dateAmountService->getReturnsCount($date);

        $paymentModesTotal = $purchaseLineRepository->todayTotalByPaymentMode($date);

        return $this->render('purchase/_resume.html.twig', [
            'date' => $date,
            'day_total' => $total,
            'list' => $list,
            'accounts' => $accounts,
            'returns' => $returns,
            'payment_modes' => $paymentModesTotal
        ]);

    }


    /**
     * @param string $paymentMode
     * @param Request $request
     * @param PurchaseRepository $purchaseRepository
     * @return JsonResponse
     *
     * Enregistre le paiement d'une ardoise
     */
    #[Route('/checkout/{paymentMode}', name: 'checkout', methods: ['POST'])]
    public function checkout(
        string $paymentMode,
        Request $request,
        PurchaseRepository $purchaseRepository
    ): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        try {
            $purchaseRepository->updatePurchases($data['ids'], $data['mode']);
            return new JsonResponse([], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }


    /**
     * @param PurchaseRepository $purchaseRepository
     * @return JsonResponse
     *
     * Renvoie les noms des personnes ayant une ardoise
     */
    #[Route('/account-names', name: 'account_names')]
    public function getAccountNames(PurchaseRepository $purchaseRepository): JsonResponse
    {
        return new JsonResponse($purchaseRepository->accountNames());
    }


    /**
     * @param EntityManagerInterface $manager
     * @param ReturnableGlassReturnRepository $returnRepository
     * @param string|null $date
     * @return JsonResponse
     *
     * Enregistre un retour de consigne
     */
    #[Route('/return-glass/{date}', name: 'return_glass', methods: ['POST'])]
    public function returnGlass(
        EntityManagerInterface $manager,
        ReturnableGlassReturnRepository $returnRepository,
        PurchaseLineRepository $purchaseLineRepository,
        ReturnableService $returnableService,
        ?string $date = null
    ): JsonResponse
    {

        $returnables = $returnableService->getReturnables($date);

        if($returnables <= 0) {
            return new JsonResponse([ ], Response::HTTP_NO_CONTENT);
        }

        $returnable = new ReturnableGlassReturn();
        $manager->persist($returnable);
        $manager->flush();

        $returns = $returnRepository->count([
            'returnedAt' => $date
        ]);

        return new JsonResponse(['count' => $returns], Response::HTTP_OK);

    }


    #[Route('/returnables/{date}', name: 'returnables_glass', methods: ['POST'])]
    public function returnables(
        EntityManagerInterface $manager,
        ReturnableGlassReturnRepository $returnRepository,
        PurchaseLineRepository $purchaseLineRepository,
        ReturnableService $returnableService,
        ?string $date = null
    ): JsonResponse
    {

        $date = new \DateTime($date) ?? new \DateTime();

        $returnables = $returnableService->getReturnables($date);

        return new JsonResponse(['count' => $returnables], Response::HTTP_OK);

    }


    /**
     * @param ReturnableGlassReturnRepository $returnRepository
     * @param string|null $date
     * @return JsonResponse
     *
     * Compte les consignes retournées par jour
     */
    #[Route('/returnable-returns/{date}', name: 'returnable_returns')]
    public function returnableReturns(
        ReturnableGlassReturnRepository $returnRepository,
        ?string $date = null
    ): JsonResponse
    {

        $date = new \DateTime($date) ?? new \DateTime();

        $returns =  $returnRepository->findBy([
            'returnedAt' => $date
        ]);

        return new JsonResponse(['count' => count($returns)], Response::HTTP_OK);

    }

    #[Route('/purchase/delete/{purchase}', name: 'delete_purchase', methods: ['DELETE'])]
    public function deletePurchase(
        Purchase $purchase,
        EntityManagerInterface $manager
    ): JsonResponse
    {

        $manager->remove($purchase);
        $manager->flush();

        return new JsonResponse(['deleted' => true], Response::HTTP_CREATED);

    }

}


