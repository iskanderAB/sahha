<?php

namespace App\Repository;

use App\Entity\SuccessStory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SuccessStory|null find($id, $lockMode = null, $lockVersion = null)
 * @method SuccessStory|null findOneBy(array $criteria, array $orderBy = null)
 * @method SuccessStory[]    findAll()
 * @method SuccessStory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuccessStoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuccessStory::class);
    }

    // /**
    //  * @return SuccessStory[] Returns an array of SuccessStory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SuccessStory
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
