<?php

namespace App\Form;

use App\Entity\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nameRoom')
            ->add('area')
            ->add('state', ChoiceType::class, [
                'choices' => [
                    'Available' => true,
                    'Unavailable' => false,
                ],
                'expanded' => false, // to display as dropdown list
                'multiple' => false, // to allow selecting only one option
                'placeholder' => 'Select a state', // optional placeholder text
            ])
            ->add('description')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
