<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        ->add('Search', SubmitType::class, [
            'attr' => [
                'style' => 'font-family: cursive;
                font-size: 15px;
                line-height: 1.5;
                color: #fff;
                height: 38px;
                text-transform: uppercase;
                border-color: #BD2A2E;
                border-radius: 10px;
                background: #BD2A2E !important;
                position: relative;
                bottom: 18px;
                left: 980px;',
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
