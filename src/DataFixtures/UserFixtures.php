<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use \DateTime;
use Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

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

        $manager->flush();
    }
}
