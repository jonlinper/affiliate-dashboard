<?php

namespace App\Repository;

use App\Entity\Sale;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sale>
 *
 * @method Sale|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sale|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sale[]    findAll()
 * @method Sale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sale::class);
    }

    public function save(Sale $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sale $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    private function findNewestHandler()
    {
        return $qb = $this->createQueryBuilder('s')
            ->orderBy('s.buyDate', 'DESC')
        ;
    }

    public function findByUserNewestQueryBuilder(User $user)
    {
        $qb = $this->findNewestHandler();

        $qb->innerJoin('s.visit', 'v', 'WITH', 'v.user = :userId');
        $qb->setParameter('userId', $user->getId());

        return $qb;
    }

    public function findNewestQueryBuilder()
    {
        return $this->findNewestHandler();
    }

    private function findNewestLast5Handler()
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.buyDate', 'DESC')
            ->setMaxResults(5)
        ;
    }

    public function findByUserNewestLast5(User $user)
    {
        $qb = $this->findNewestLast5Handler();

        $qb->innerJoin('s.visit', 'v', 'WITH', 'v.user = :userId');
        $qb->setParameter('userId', $user->getId());

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNewestLast5()
    {
        return $this->findNewestLast5Handler()
            ->getQuery()
            ->getResult()
        ;
    }

    private function countPerDayHandler($qb)
    {
        $res = array();
        $counter = array();

        // Initialize Counter
        $datetime = new \DateTime();
        for($i = 1; $i <= 30; $i++) {
            $date = $datetime->format('dMY');
            $counter[$date] = 0;
            $datetime->modify('-1 day');
        }

        // Get last 30 days
        $fromDate = new \DateTime();
        $fromDate->modify('-30 days');
        $sales = $qb
            ->andWhere('s.buyDate > :fromDate')
            ->setParameter('fromDate', $fromDate)
            ->getQuery()
            ->getResult()
        ;

        // Count
        foreach($sales as $sale) {
            $date = $sale->getBuyDate()->format('dMY');
            $counter[$date]++;
        }

        // Build result
        foreach($counter as $dayCount) {
            array_push($res, $dayCount);
        }

        return array_reverse($res);
    }

    public function countByUserPerDay(User $user)
    {
        $qb = $this->createQueryBuilder('s')
            ->innerJoin('s.visit', 'v', 'WITH', 'v.user = :userId')
            ->setParameter('userId', $user->getId())
        ;

        return $this->countPerDayHandler($qb);
    }

    public function countPerDay()
    {
        $qb = $this->createQueryBuilder('s');

        return $this->countPerDayHandler($qb);
    }

//    /**
//     * @return Sale[] Returns an array of Sale objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sale
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
