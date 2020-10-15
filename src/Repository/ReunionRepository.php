<?php

namespace App\Repository;

use App\Entity\Reunion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use \DateTime;


/**
 * @method Reunion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reunion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reunion[]    findAll()
 * @method Reunion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReunionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reunion::class);
    }

    /**
     * @return Reunion[] Returns an array of Reunion objects
     */
    public function findPastReunions() : array
    {
        $datetime = new DateTime();
        $tomorrow = $datetime->modify('-1 day');
        date_format($datetime, 'Y-m-d H:i:s');

        // Retourne ce que la requeête DQL aura trouvé en BDD
        return $this->createQueryBuilder('r')
        ->where('r.datetime < :tomorrow')
        ->setParameter('tomorrow', $tomorrow)
        ->getQuery()
        ->getResult()
        ;
    }
}
