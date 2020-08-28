<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\ENtity\Gallery;
use Doctrine\ORM\Query\Expr\Join;
use \DateTime;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
    * @return Article[] Returns an array of Article objects
    */
    public function findFourLatest() : array
    {
        $datetime = new DateTime();
        $now = date_format($datetime, 'Y-m-d H:i:s');

        // Retourne ce que la requête DQL aura trouvé en BDD
        return $this->createQueryBuilder('a')   // a = alias de la table "article"
        ->where('a.dateBeginning > :now')
        ->setParameter('now', $now)
        ->orderBy('a.dateBeginning', 'ASC')
        ->setMaxResults(4)
        ->getQuery()    // Execution de la requête
        ->getResult()   // Récupération du résultat de la requête
        ;
    }

    /**
     * @return Article[] Returns an arry of Article objects
     */
    public function findAllWithGallery() : array
    {
        // Retourne ce que la requête DQL aura trouvé en BDD
        return $this->createQueryBuilder('a')   // a = alias
        ->innerJoin(
            Gallery::class, // Entité
            'g',            // Alias
            Join::WITH,     // Join type
            'g.article = a.id'
        )
        ->orderBy('a.dateEnd', 'DESC')
        ->getQuery()        // Exécution de la requête
        ->getResult()       // Récupération du résultat
        ;
    }

}
