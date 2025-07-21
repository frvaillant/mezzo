<?php

namespace App\Service;

use App\Repository\PurchaseLineRepository;
use App\Repository\ReturnableGlassReturnRepository;

readonly class DateAmountService
{

    public function __construct(
        private PurchaseLineRepository          $purchaseLineRepository,
        private ReturnableGlassReturnRepository $returnRepository,
    )
    {

    }

    /**
     * @param \DateTime $date
     * @return \App\Entity\PurchaseLine[]|float
     *
     * Total des ventes du jour
     */
    public function getSoldAMount(\DateTime $date)
    {
        return $this->purchaseLineRepository->todayTotal($date);
    }

    /**
     * @param \DateTime $date
     * @return int
     *
     * Nombre de consignes revenues
     * TODO : gérer prix de la consigne, par défaut 1euro
     */
    public function getReturnsCount(\DateTime $date)
    {
        return $this->returnRepository->count([
            'returnedAt' => $date
        ]);
    }

    /**
     * @param \DateTime $date
     * @return \App\Entity\PurchaseLine[]|float|int
     *
     * Renvoie le montant réel des ventes du jour
     */
    public function getRealDateAmount(\DateTime $date)
    {
        return $this->getSoldAMount($date) - $this->getReturnsCount($date);
    }



}
