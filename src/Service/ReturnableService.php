<?php

namespace App\Service;

use App\Repository\PurchaseLineRepository;
use App\Repository\ReturnableGlassReturnRepository;

class ReturnableService
{

    public function __construct(
        private ReturnableGlassReturnRepository $returnRepository,
        private PurchaseLineRepository $purchaseLineRepository
    ) {

    }


    public function getReturnables(?\DateTime $date = null ): int
    {
        $date = $date ?? new \DateTime();

        $returns = $this->returnRepository->count([
            'returnedAt' => $date
        ]);

        $returnables = $this->purchaseLineRepository->getReturnables($date);

        return (int)($returnables - $returns);
    }

}
