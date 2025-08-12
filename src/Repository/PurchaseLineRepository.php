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


    public function getReturnables(?\DateTime $date = null): float
    {
        $today = $date ?? new \DateTime();
        $start = (clone $today)->setTime(0, 0, 0);
        $end = (clone $today)->setTime(23, 59, 59);

        return $this->createQueryBuilder('pl')
            ->join('pl.purchase', 'p')
            ->where('p.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->select('SUM(pl.consigne) AS total')
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

    public function todayTotalSoldByProduct(?\DateTime $date = null): array
    {
        $today = $date ?? new \DateTime();
        $start = (clone $today)->setTime(0, 0, 0);
        $end = (clone $today)->setTime(23, 59, 59);

        return $this->createQueryBuilder('pl')
            ->join('pl.purchase', 'p')
            ->join('pl.product', 'pro')
            ->where('p.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->select('COALESCE(SUM(pl.quantity), 0) AS quantity')
            ->addSelect('pro.name')
            ->addSelect('pro.unitPrice')
            ->groupBy('pl.product')
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


    public function getDailyReturnables(\DateTimeInterface $date): float
    {
        $start = (clone $date)->setTime(0, 0, 0);
        $end = (clone $date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('pl')
            ->select('COALESCE(SUM(pl.consigne), 0) AS total')
            ->join('pl.purchase', 'p')
            ->where('p.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
//            ->groupBy('p.createdAt')
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function getTotalSoldByDay(int $year): array
    {
        $start = new \DateTime("$year-01-01");
        $end = new \DateTime("$year-12-31");

        $results = $this->createQueryBuilder('pl')
            ->join('pl.purchase', 'p')
            ->select('DATE(p.createdAt) AS date')
            ->addSelect('COALESCE(SUM(pl.total), 0) AS total')
            ->where('p.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE
        );

        foreach ($results as &$row) {
            $dateObj = \DateTime::createFromFormat('Y-m-d', $row['date']);
            $row['date'] = ucfirst($formatter->format($dateObj));
        }

        return $results;
    }


    public function getTotalSoldBySeason(int $year): int
    {
        $start = new \DateTime("$year-01-01");
        $end = new \DateTime("$year-12-31");

        return $this->createQueryBuilder('pl')
            ->join('pl.purchase', 'p')
            ->select('COALESCE(SUM(pl.total), 0) AS total')
            ->where('p.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult()
        ;

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
