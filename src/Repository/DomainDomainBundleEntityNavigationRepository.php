<?php

namespace App\Repository;

use App\Entity\DomainDomainBundleEntityNavigation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DomainDomainBundleEntityNavigation>
 *
 * @method DomainDomainBundleEntityNavigation|null find($id, $lockMode = null, $lockVersion = null)
 * @method DomainDomainBundleEntityNavigation|null findOneBy(array $criteria, array $orderBy = null)
 * @method DomainDomainBundleEntityNavigation[]    findAll()
 * @method DomainDomainBundleEntityNavigation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DomainDomainBundleEntityNavigationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainDomainBundleEntityNavigation::class);
    }

//    /**
//     * @return DomainDomainBundleEntityNavigation[] Returns an array of DomainDomainBundleEntityNavigation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DomainDomainBundleEntityNavigation
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
