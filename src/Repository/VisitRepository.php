<?php

namespace App\Repository;

use App\Entity\Visit;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visit>
 *
 * @method Visit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Visit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Visit[]    findAll()
 * @method Visit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visit::class);
    }

    public function save(Visit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Visit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function storeNavigation(Visit $visit): void
    {
        $visit->setCreatedAt(new \DateTimeImmutable());

        $this->getEntityManager()->persist($visit);
        $this->getEntityManager()->flush();
    }

    private function findNewestHandler()
    {
        return $qb = $this->createQueryBuilder('v')
            ->orderBy('v.createdAt', 'DESC')
        ;
    }

    public function findByUserNewestQueryBuilder(User $user)
    {
        $qb = $this->findNewestHandler();

        $qb->andWhere('v.user = :userId');
        $qb->setParameter('userId', $user->getId());

        return $qb;
    }

    public function findNewestQueryBuilder()
    {
        return $this->findNewestHandler();
    }

    private function findNewestLast5Handler()
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.createdAt', 'DESC')
            ->setMaxResults(5)
        ;
    }

    public function findByUserNewestLast5(User $user)
    {
        $qb = $this->findNewestLast5Handler();

        $qb->andWhere('v.user = :userId');
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
        $fromDate->modify('-29 days'); // 29 + current day.
        $visits = $qb
            ->andWhere('v.createdAt > :fromDate')
            ->setParameter('fromDate', $fromDate)
            ->getQuery()
            ->getResult()
        ;

        // Count
        foreach($visits as $visit) {
            $date = $visit->getCreatedAt()->format('dMY');
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
        $qb = $this->createQueryBuilder('v')
            ->andWhere('v.user = :userId')
            ->setParameter('userId', $user->getId())
        ;

        return $this->countPerDayHandler($qb);
    }

    public function countPerDay()
    {
        $qb = $this->createQueryBuilder('v');

        return $this->countPerDayHandler($qb);
    }

//    /**
//     * @return Visit[] Returns an array of Visit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Visit
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
