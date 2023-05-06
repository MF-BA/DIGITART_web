<?php

namespace App\DataFixtures;

use App\Entity\Artwork;
use App\Entity\Auction;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class AuctionFixture extends Fixture
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        

        
        for ($i = 208; $i <= 225; $i++) {
$auction = new Auction();
            $auction->setStartingPrice(2500);
            $auction->setIncrement(100);
            $auction->setEndingDate(new DateTime('2023-10-01'));
            $auction->setDescription("'As an artist, I pour my heart and soul into every piece I create, and I'm thrilled to share my work with you. Each of my artworks is a unique expression of my creativity, inspired by my life experiences and the world around me. I believe that art has the power to inspire, uplift, and transform, and I'm excited to offer you a chance to own a piece of that magic.So, if you're looking for an affordable, high-quality piece of art to add to your collection, look no further than my artwork.'");


            $artwork = $this->entityManager->getRepository(Artwork::class)->find($i);
            $auction->setartwork($artwork);

            $manager->persist($auction);
        }
        $manager->flush();
    }
}
