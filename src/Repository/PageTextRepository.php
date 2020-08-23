<?php

namespace App\Repository;

use App\Entity\PageText;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PageText|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageText|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageText[]    findAll()
 * @method PageText[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageTextRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageText::class);
    }

    /*
    public function findOneBySomeField($value): ?PageText
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
