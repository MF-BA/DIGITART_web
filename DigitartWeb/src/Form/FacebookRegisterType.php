<?php

namespace App\Form;
use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
class FacebookRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('cin', IntegerType::class, [
            'label' => 'Cin',
            'attr' => [
                'placeholder' => 'Enter cin',
                'class' => 'form-control',
                'id' => 'cin-input', // Define the id attribute here
            ],
        ])
        
        
    
    ->add('password', PasswordType::class, [
       
        'attr' => ['autocomplete' => 'new-password'],
    ])

        ->add('address', TextType::class, [
            'label' => 'Address',
            'attr' => [
                'placeholder' => 'Enter Address',
                'class' => 'form-control',
                'id' => 'address-input', // Define the id attribute here
            ],
        ])
        ->add('phoneNum', IntegerType::class, [
            'label' => 'Phone Number',
            'attr' => [
                'placeholder' => 'Enter phone number',
                'class' => 'form-control',
                'id' => 'phoneNum-input', // Define the id attribute here
            ],
        ])
        ->add('birthDate',BirthdayType::class, [
            'label' => 'Birth date',
            'widget' => 'single_text',
            'attr' => ['class' => 'form-control', 'max' => (new \DateTime())->format('Y-m-d')],
            
        ])
        ->add('gender', ChoiceType::class, [
            'choices' => [
                'Male' => 'Male',
                'Female' => 'Female',
               
            ],
            'expanded' => true,
            'multiple' => false,
            
        ])
        ->add('role', ChoiceType::class, [
            'choices' => [
                'Artist' => 'Artist',
                'Subscriber' => 'Subscriber',
            ],
            'expanded' => false,
            'multiple' => false,
            'placeholder' => 'Choose your role',
            
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
