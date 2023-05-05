<?php

namespace App\DataFixtures;

use App\Entity\Room;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class RoomFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        for($nbRoom = 1; $nbRoom <= 10; $nbRoom++){
            $room = new Room();
            $room->setNameRoom($faker->city);
            $room->setArea($faker->numberBetween(10, 1000));
            $room->setDescription($faker->realText(60));
            $room->setState($faker->randomElement(['Available', 'Unavailable']));

           
            $manager->persist($room);

            // Enregistre la room dans une référence
           // $this->addReference('room_'. $nbRoom, $room);
        }

        $manager->flush();
    }
}
