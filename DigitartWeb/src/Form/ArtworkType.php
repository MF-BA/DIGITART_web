<?php

namespace App\Form;

use App\Entity\Artwork;
use App\Entity\Room;
use App\Entity\Users;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType ;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class ArtworkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('artworkName')
            ->add('idArtist', EntityType::class, [
                'class' => Users::class,
                'label' => 'existing artist ',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.role = :role')
                        ->setParameter('role', 'Artist')
                        ->orderBy('u.lastname', 'ASC');
                },
                'choice_label' => 'lastname',
                'placeholder' => 'Select an Artist', // optional placeholder text
            ])
            ->add('artistName', TextType::class, [
                'attr' => ['id' => 'artistName']
            ])
            ->add('dateArt', DateType::class, [
                'widget' => 'single_text',
                'label' => 'artwork date ',
                'attr' => ['max' => (new \DateTime())->format('Y-m-d')],
                
            ])
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
