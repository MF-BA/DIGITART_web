<?php

namespace App\Form;

use App\Entity\Artwork;
use App\Entity\Room;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtworkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('artworkName')
            ->add('idArtist')
            ->add('artistName')
            ->add('dateArt')
            ->add('description')
            ->add('imageArt')
            ->add('idRoom', EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'nameRoom',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artwork::class,
        ]);
    }
}
