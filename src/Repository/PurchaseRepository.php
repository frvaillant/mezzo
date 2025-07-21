<?php

namespace App\Repository;

use App\Entity\Purchase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
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


    public function accountNames(?\DateTime $date = null): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT(p.account) as name')
            ->where('p.account IS NOT NULL')
            ->getQuery()
            ->getResult(Query::HYDRATE_SCALAR_COLUMN)
            ;

    }

    public function updatePurchases(array $ids, string $mode): void
    {
        $qb = $this->createQueryBuilder('p');

        $qb->update()
            ->set('p.account', ':null')
            ->set('p.paymentMode', ':mode')
            ->where($qb->expr()->in('p.id', ':ids'))
            ->setParameter('null', null)
            ->setParameter('mode', $mode)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }

}
