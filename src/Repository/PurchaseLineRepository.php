<?php

namespace App\Repository;

use App\Entity\PurchaseLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PurchaseLine>
 */
class PurchaseLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseLine::class);
    }

        /**
         * @return PurchaseLine[] Returns an array of PurchaseLine objects
         */
        public function todayTotal(?\DateTime $date = null): float
        {
            $today = $date ?? new \DateTime();
            $start = (clone $today)->setTime(0, 0, 0);
            $end = (clone $today)->setTime(23, 59, 59);

            return $this->createQueryBuilder('pl')
                ->join('pl.purchase', 'p')
                ->where('p.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end)
                ->select('SUM(pl.total + pl.consigne) AS total_sum')
                ->getQuery()
                ->getSingleScalarResult() ?? 0;
        }


    /**
     * @return PurchaseLine[] Returns an array of PurchaseLine objects
     */
    public function todayTotalByPaymentMode(?\DateTime $date = null): array
    {
        $today = $date ?? new \DateTime();
        $start = (clone $today)->setTime(0, 0, 0);
        $end = (clone $today)->setTime(23, 59, 59);

        return $this->createQueryBuilder('pl')
            ->join('pl.purchase', 'p')
            ->where('p.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->select('COALESCE(SUM(pl.total + pl.consigne), 0) AS total')
            ->addSelect('COALESCE(p.paymentMode, :defaultMode) AS mode')
            ->setParameter('defaultMode', 'En compte')
            ->groupBy('p.paymentMode')
            ->getQuery()
            ->getResult();
    }

    public function getDailyTotalByAccount(\DateTimeInterface $date): array
    {
        $start = (clone $date)->setTime(0, 0, 0);
        $end = (clone $date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('pl')
            ->select('p.account AS name')
            ->addSelect('SUM(pl.total + pl.consigne) AS total')
            ->join('pl.purchase', 'p')
            ->where('p.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->andWhere('p.account IS NOT NULL')
            ->groupBy('name')
            ->getQuery()
            ->getResult();
    }


    //    public function findOneBySomeField($value): ?PurchaseLine
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
