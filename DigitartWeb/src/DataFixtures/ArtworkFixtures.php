<?php

namespace App\DataFixtures;

use App\Entity\Artwork;
use App\Entity\ImageArtwork;
use App\Entity\Room;
use App\Repository\RoomRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class ArtworkFixtures extends Fixture
{
    private $roomRepository;
    public function __construct(RoomRepository $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        
    

        for($nbArtwork = 1; $nbArtwork <= 30; $nbArtwork++){
            //$room = $this->getReference('room_'. $faker->numberBetween(1, 10));
            
            
            $artwork = new Artwork();
            $idr=$faker->numberBetween(37, 41);
            $artwork->setIdRoom($this->roomRepository->find($idr));
            $artwork->setartworkName($faker->realText(25));
            $artwork->setArtistName($faker->Lastname);
            $artwork->setDateArt($faker->dateTimeBetween($startDate = '-200 years', $endDate = 'now', $timezone = null));
            $artwork->setDescription($faker->realText(60));

         
            $manager->persist($artwork);
        }
        $manager->flush();
    }
}
