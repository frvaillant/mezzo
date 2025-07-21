<?php

namespace App\Repository;

use App\Entity\Purchase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Purchase>
 */
class PurchaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }



        public function dayList(?\DateTime $date = null): array
        {
            $today = $date ?? new \DateTime();
            $start = (clone $today)->setTime(0, 0, 0);
            $end = (clone $today)->setTime(23, 59, 59);

            return $this->createQueryBuilder('p')
                ->where('p.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end)
                ->getQuery()
                ->getResult()
            ;
        }
}
