<?php

namespace App\Form;
use App\Entity\Artwork;
use App\Entity\Auction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\ArtworkRepository;

class Auction1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startingPrice')
            ->add('increment')
            ->add('endingDate')
            ->add('description')
            ->add('artwork', EntityType::class, [
                'class' => Artwork::class,
                'choice_label' => 'artworkName',
                'query_builder' => function (ArtworkRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.idArtist != :excluded_id')
                        ->setParameter('excluded_id', -1);
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Auction::class,
        ]);
    }
}
