<?php

namespace App\Repository;

use App\Entity\TestUnitTesting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TestUnitTesting|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestUnitTesting|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestUnitTesting[]    findAll()
 * @method TestUnitTesting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestUnitTestingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TestUnitTesting::class);
    }

//    /**
//     * @return TestUnitTesting[] Returns an array of TestUnitTesting objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TestUnitTesting
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
