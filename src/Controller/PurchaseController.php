<?php

namespace App\Controller;

use App\Dto\PurchaseDto;
use App\Dto\PurchaseProductDto;
use App\Entity\Purchase;
use App\Entity\PurchaseLine;
use App\Repository\ProductRepository;
use App\Repository\PurchaseLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PurchaseController extends AbstractController
{


    #[Route('/purchase', name: 'purchase', methods: ['POST'])]
    public function purchase(
        Request $request,
        SerializerInterface $serializer,
        ProductRepository $productRepository,
        EntityManagerInterface $manager
    )
    {
        $data = json_decode($request->getContent(), true);

        if(!empty($data)) {

            try {
                $purchase = new Purchase();
                $purchase->setPaymentMode($data['mode']);
                $manager->persist($purchase);

                foreach ($data['purchase'] as $productId => $cart) {
                    $cart = new PurchaseProductDto($cart);
                    $product = $productRepository->find($productId);

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


    #[Route('/daytotal', name: 'day_total', methods: ['POST'])]
    public function getDayTotal(PurchaseLineRepository $purchaseLineRepository): JsonResponse
    {
        return new JsonResponse(['total' => $purchaseLineRepository->todayTotal()], Response::HTTP_OK);
    }

}
