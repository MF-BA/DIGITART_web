<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SearchUsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('mots', SearchType::class, [
            'label' => false,
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Search for users',
                'style' => 'width: 800px;
                position: relative;
                left: 230px;
                top: 20px;'
            ],
            'required' => false
        ])
        ->add('role', ChoiceType::class, [
            'choices' => [
                'Admin' => 'Admin',
                'Artist' => 'Artist',
                'Subscriber' => 'Subscriber',
                'Gallery manager' => 'Gallery Manager',
                'Auction manager' => 'Auction Manager',
                'Events manager' => 'Events Manager',
                'Tickets manager' => 'Tickets Manager',
                'Users manager' => 'Users Manager',
            ],
            'expanded' => false,
            'multiple' => false,
            'label' => false,
            'placeholder' => 'Search with role',
            'attr' => [
                'class' => 'form-control',
                'style' => 'width: 200px;
                position: relative;
                left: 230px;
                top: 20px;'
            ],
            'required' => false
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['id' => 'search-form']
        ]);
    }
}
