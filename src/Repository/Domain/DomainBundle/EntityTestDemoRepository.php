<?php

namespace App\Repository\Domain\DomainBundle;

use App\Entity\Domain\DomainBundle\EntityTestDemo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EntityTestDemo>
 *
 * @method EntityTestDemo|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntityTestDemo|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntityTestDemo[]    findAll()
 * @method EntityTestDemo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityTestDemoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityTestDemo::class);
    }

//    /**
//     * @return EntityTestDemo[] Returns an array of EntityTestDemo objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EntityTestDemo
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
