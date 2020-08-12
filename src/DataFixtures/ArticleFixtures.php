<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Article;
use \DateTime;
use Faker;


class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // On instancie le Faker en langue française
        $faker = Faker\Factory::create('fr_FR');

        // Boucle de 20 itérations
        for($i = 1; $i <= 20; $i++){

            // Création d'un nouvel article
            $newArticle = new Article();

            // Hydratation du nouvel article
            $newArticle
                ->setTitle('Article ' . $i) 
                ->setDescription($faker->paragraph(5))
                ->setPublicationDate( new DateTime() )
                ->setMainPhoto($faker->file('public/images/articlesFixtures', 'public/images/articles', false))
                ->setDateBeginning($faker->dateTimeBetween('-1year', '+1year'))
                ->setDateEnd($faker->dateTimeBetween('-1year', '+1year'))
            ;

            // Enregistrement du nouvel article auprès de Doctrine
            $manager->persist($newArticle);

        }

        // Sauvegarde en BDD des articles
        $manager->flush();

    }
}
