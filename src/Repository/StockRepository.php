<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }


    public function getStocksAlert()
    {
        $results = $this->createQueryBuilder('s')
            ->join('s.product', 'p')
            ->select('p.id')
            ->addSelect('s.quantity')
            ->addSelect('s.alertThreshold')
            ->getQuery()
            ->getResult()
            ;

        $data = [];
        foreach ($results as $row) {
            $data[$row['id']] = $row['quantity'] > 0 && $row['quantity'] <= $row['alertThreshold'];
        }

        return $data;
    }

//        /**
//         * @return Stock[] Returns an array of Stock objects
//         */
//        public function findByExampleField($value): array
//        {
//            return $this->createQueryBuilder('s')
//                ->andWhere('s.exampleField = :val')
//                ->setParameter('val', $value)
//                ->orderBy('s.id', 'ASC')
//                ->setMaxResults(10)
//                ->getQuery()
//                ->getResult()
//            ;
//        }

    //    public function findOneBySomeField($value): ?Stock
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
