<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Article;
use App\Entity\User;
use App\Entity\Participation;
use \DateTime;
use Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    /**
     * Utilisation du constructeur pour récupérer le service de hashage des mots de passe via autowiring
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // On instancie le Faker en langue française
        $faker = Faker\Factory::create('fr_FR');

        // Création d'un compte admin
        $newAdmin = new User();

        $newAdmin
            ->setEmail('alicedu71@gmail.com')
            ->setRoles(["ROLE_ADMIN"])
            ->setPassword($this->encoder->encodePassword($newAdmin, 'Alicedu71@'))
            ->setRegistrationDate(new DateTime())
            ->setName('Durand')
            ->setFirstname('Alice')
            ->setBirthdate($faker->dateTimeBetween('-90year', '-18year'))
            ->setPhone($faker->phoneNumber)
            ->setMotivation('Je suis l\'admin, ma motivation, c\'est de mettre de l\'ordre !!')
        ;

        $manager->persist($newAdmin);

        // Boucle de 20 itérations
        for($i = 1; $i <= 20; $i++ ){

            // Création d'un nouvel user
            $newUser = new User();

            // Hydratation de chaque user
            $newUser
                ->setEmail($faker->email)
                ->setPassword($this->encoder->encodePassword($newUser, $faker->password))
                ->setRegistrationDate($faker->dateTimeBetween('-3year', 'now'))
                ->setName($faker->lastName)
                ->setFirstname($faker->firstName)
                ->setBirthdate($faker->dateTimeBetween('-90year', '-18year'))
                ->setPhone($faker->phoneNumber)
                ->setMotivation('Je suis un bot. Je suis donc extrêmement motivé !')
            ;

            // Enregistrement auprès de Doctrine
            $manager->persist($newUser);

        }

        // Boucle de 20 itérations
        for($i = 1; $i <= 20; $i++){

            // Création d'un nouvel article
            $newArticle = new Article();

            // Hydratation du nouvel article
            $newArticle
                ->setTitle('Article ' . $i) 
                ->setDescription($faker->realText($maxNbChars = 200, $indexSize = 2))
                ->setPublicationDate( new DateTime() )
                ->setMainPhoto($faker->file('public/images/articlesFixtures', 'public/images/articles', false))
                ->setDateBeginning($faker->dateTimeBetween('-1year', '+1year'))
                ->setDateEnd($faker->dateTimeInInterval($newArticle->getDateBeginning(), '+5 days'))
                ->setParticipationsCounter(0)
            ;

            // Stockage des articles de côté
            $articles[] = $newArticle;

            // Enregistrement du nouvel article auprès de Doctrine
            $manager->persist($newArticle);

        }

        // Sauvegarde en BDD des articles
        $manager->flush();

    }
}
