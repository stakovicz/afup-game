<?php

namespace App\Repository;

use App\Entity\Point;
use App\Game\Engine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Point>
 */
class PointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Point::class);
    }

    public function scores(int $stage): array
    {
        $scores =  $this->createQueryBuilder('point')
            ->select('SUM(point.value) AS score, point.team')
            ->andWhere('point.stage = :stage')
            ->setParameter('stage', $stage)
            ->groupBy('point.team')
            ->getQuery()
            ->getArrayResult();
        $scores = array_column($scores, 'score', 'team');

        return array_merge(array_fill_keys(Engine::TEAMS, 0), $scores);
    }

    //    /**
    //     * @return Point[] Returns an array of Point objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Point
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
