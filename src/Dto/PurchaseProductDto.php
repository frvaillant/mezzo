<?php

namespace App\Dto;

class PurchaseProductDto
{

    public float $total;
    public float $totalProduct;
    public int $quantity;
    public bool $hasConsigne;

    public function __construct($data)
    {
        $this->quantity = $data['quantity'];
        $this->total = $data['total'];
        $this->totalProduct = $data['totalProduct'];
        $this->hasConsigne = $data['hasConsigne'];
    }

}
