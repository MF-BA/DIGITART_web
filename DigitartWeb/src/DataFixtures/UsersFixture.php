<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UsersFixture extends Fixture
{
    private $userPasswordHasher;
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
       
    }
    public function load(ObjectManager $manager): void
    {
        
        $faker = Factory::create();
           
        for($i=0; $i<50; $i++)
        {
         $user = new Users();
         $user->setFirstname($faker->firstName);
         $user->setLastname($faker->lastName);
         $user->setEmail($faker->email); 
         $user->setAddress($faker->address);
         $user->setPhoneNum($faker->numberBetween(11111111, 99999999));
         $user->setBirthDate($faker->dateTimeBetween('-1 year', 'now'));
         $user->setImage('0776dc120d4e09a5a2f635b1fb51e93e.jpg');
         $user->setPassword($this->userPasswordHasher->hashPassword($user,$faker->password()));
         $user->setGender($faker->randomElement(['Male', 'Female']));
         $user->setRole($faker->randomElement(['Admin', 'Subscriber', 'Artist', 'Gallery Manager',
         'Auction Manager',
         'Event Manager',
         'Tickets Manager',
         'Users Manager' ]));
        
         $manager->persist($user);
        }

        $manager->flush();
    }
}
