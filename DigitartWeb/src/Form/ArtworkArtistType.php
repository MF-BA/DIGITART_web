<?php

namespace App\Form;

use App\Entity\Artwork;
use App\Entity\Room;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtworkArtistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('artworkName')

            ->add('dateArt', DateType::class, [
                'widget' => 'single_text',
                'label' => 'artwork date ',
                'attr' => ['max' => (new \DateTime())->format('Y-m-d')],
                
            ])
            ->add('description')
            ->add('imageArt', FileType::class, [
                'label' => 'Image file',
                'required' => false, // set to false so the user can submit the form without an image

            ])
           
            ->add('idRoom', EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'nameRoom',
                 'data_class' => null,
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
