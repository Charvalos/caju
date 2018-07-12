<?php

namespace App\Repository;

use App\Entity\ClosingType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ClosingType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClosingType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClosingType[]    findAll()
 * @method ClosingType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClosingTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ClosingType::class);
    }

//    /**
//     * @return ClosingType[] Returns an array of ClosingType objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ClosingType
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
