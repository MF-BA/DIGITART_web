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
          
           
            ->add('idRoom', EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'nameRoom',
                 'data_class' => null,
            ])

            ->add('images', FileType::class, [
                'label' => false,
                'multiple' => true,
                'mapped' => false,
                'required' => false
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
