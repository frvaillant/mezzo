<?php

namespace App\Service;

use App\Dto\PurchaseProductDto;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class StockManager
{

    public function __construct( private EntityManagerInterface $manager )
    {

    }

    public function decreaseStock(Product $product, float $deliveredQuantity): void
    {
        $productStock = $product->getStock();

        if($productStock) {

            $stockQuantity = $productStock->getQuantity();
            $cartQuantity = $deliveredQuantity * $product->getSaleUnit();
            $stockQuantity -= $cartQuantity;

            if($stockQuantity < 0) {
                $stockQuantity = 0;
            }

            $productStock->setQuantity($stockQuantity);
            $this->manager->persist($productStock);
            $this->manager->flush();

        }
    }

}
