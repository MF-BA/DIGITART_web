<?php

namespace App\Form;

use App\Entity\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nameRoom')
            ->add('area', IntegerType::class, [
                'label' => 'Area',
                'attr' => [
                    'input' => 'spinner',
                    'step' => 10,
                    'min' => 0,
                ],
            ])
            ->add('state', ChoiceType::class, [
                'choices' => [
                    'Available' => 'Available',
                    'Unavailable' => 'Unavailable',
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
