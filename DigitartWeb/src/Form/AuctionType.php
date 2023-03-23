<?php

namespace App\Form;
use App\Entity\Artwork;
use App\Entity\Auction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class AuctionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startingPrice')
            ->add('increment')
            ->add('endingDate')
            ->add('description')
            ->add('idArtwork', EntityType::class, [
                'class' => Artwork::class,
                'choice_label' => 'artworkName',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Auction::class,
        ]);
    }
}
