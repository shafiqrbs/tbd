<?php

namespace App\Repository\Domain\DomainBundle;

use App\Entity\Domain\DomainBundle\EntityTest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EntityTest>
 *
 * @method EntityTest|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntityTest|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntityTest[]    findAll()
 * @method EntityTest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityTestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityTest::class);
    }

//    /**
//     * @return EntityTest[] Returns an array of EntityTest objects
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

//    public function findOneBySomeField($value): ?EntityTest
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
