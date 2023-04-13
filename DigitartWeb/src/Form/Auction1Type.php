<?php

namespace App\Form;

use App\Entity\Users;
use App\Entity\Artwork;
use App\Entity\Auction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\ArtworkRepository;
use App\Repository\AuctionRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Security\Core\Security;


class Auction1Type extends AbstractType
{
    private $artworkRepository;
    private $security;

    public function __construct(ArtworkRepository $artworkRepository, Security $security)
    {
        $this->artworkRepository = $artworkRepository;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $user = $this->security->getUser();
        if ($user->getRole() == "Admin") {
            $builder
                ->add('startingPrice')
                ->add('increment')
                ->add('endingDate', DateType::class, [
                    'widget' => 'single_text',
                    'label' => 'Ending Date',
                    'attr' => ['min' => (new \DateTime('+1 day'))->format('Y-m-d')],
                    'data' => new \DateTime('+1 day'),
                ])
                ->add('description')
                ->add('artwork', EntityType::class, [
                    'class' => Artwork::class,
                    'choice_label' => 'artworkName',
                    'query_builder' => function (ArtworkRepository $er) {
                        $sub = $er->createQueryBuilder('s')
                            ->select('s.idArt, a.idArt as artwork_id')
                            ->from(Auction::class, 'ac')
                            ->join('ac.artwork', 'a')
                            ->where('ac.deleted is null')
                            ->getQuery()
                            ->getResult();

                        $artworkIds = array_map(function ($row) {
                            return $row['artwork_id'];
                        }, $sub);
                        if ($artworkIds == null) {
                            return $er->createQueryBuilder('a')
                                ->Where('a.idArtist != :excluded_id')
                                ->setParameter('excluded_id', -1);
                        } else {
                            return $er->createQueryBuilder('a')
                                ->where('a.idArt NOT IN (:artwork_ids)')
                                ->andWhere('a.idArtist != :excluded_id')
                                ->setParameter('excluded_id', -1)
                                ->setParameter('artwork_ids', $artworkIds);
                        }
                    },
                ]);
        } else if ($user->getRole() == "Artist") {
            $builder
                ->add('startingPrice')
                ->add('increment')
                ->add('endingDate', DateType::class, [
                    'widget' => 'single_text',
                    'label' => 'Ending Date',
                    'attr' => ['min' => (new \DateTime('+1 day'))->format('Y-m-d')],
                    'data' => new \DateTime('+1 day'),
                ])
                ->add('description')
                ->add('artwork', EntityType::class, [
                    'class' => Artwork::class,
                    'choice_label' => 'artworkName',
                    'query_builder' => function (ArtworkRepository $er) use ($user) {
                        $sub = $er->createQueryBuilder('s')
                            ->select('s.idArt, a.idArt as artwork_id')
                            ->from(Auction::class, 'ac')
                            ->join('ac.artwork', 'a')
                            ->where('ac.deleted is null')
                            ->getQuery()
                            ->getResult();
                        $artworkIds = array_map(function ($row) {
                            return $row['artwork_id'];
                        }, $sub);

                        if ($artworkIds == null) {
                            return $er->createQueryBuilder('a')
                                ->Where('a.idArtist is not null');
                        } else {
                            return $er->createQueryBuilder('a')
                                ->where('a.idArt NOT IN (:artwork_ids)')
                                ->andWhere('a.idArtist = :user_id')
                                ->setParameter('user_id', $user->getId())
                                ->setParameter('artwork_ids', $artworkIds);
                        }
                    },
                ]);
        }
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Auction::class,
        ]);
    }
}
