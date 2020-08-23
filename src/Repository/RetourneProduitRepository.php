<?php

namespace App\Repository;

use App\Entity\RetourneProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RetourneProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method RetourneProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method RetourneProduit[]    findAll()
 * @method RetourneProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RetourneProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RetourneProduit::class);
    }

    // /**
    //  * @return RetourneProduit[] Returns an array of RetourneProduit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RetourneProduit
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
