<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UsersFixture extends Fixture
{
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
         $user->setPassword($faker->password);
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
