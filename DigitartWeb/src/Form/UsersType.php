<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;

class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cin', TextType::class, [
                'label' => 'Cin',
                'attr' => [
                    'placeholder' => 'Enter cin',
                    'class' => 'form-control',
                    'id' => 'cin-input', // Define the id attribute here
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a cin',
                    ]),
                    new Length([
                        'min' => 8,
                        'max' => 8,
                        'exactMessage' => 'Cin must contain exactly {{ limit }} digits',
                    ]),
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Firstname',
                'attr' => [
                    'placeholder' => 'Enter first name',
                    'class' => 'form-control',
                    'id' => 'firstname-input', // Define the id attribute here
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a first name',
                    ])
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Lastname',
                'attr' => [
                    'placeholder' => 'Enter last name',
                    'class' => 'form-control',
                    'id' => 'lastname-input', // Define the id attribute here
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a last name',
                    ])
                ],
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Enter Email',
                    'class' => 'form-control',
                    'id' => 'email-input', // Define the id attribute here
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email',
                    ])
                ],
            ])
            ->add('password', TextType::class, [
                'label' => 'Password',
                'attr' => [
                    'placeholder' => 'Enter password',
                    'class' => 'form-control',
                    'id' => 'password-input', // Define the id attribute here
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ])
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Address',
                'attr' => [
                    'placeholder' => 'Enter Address',
                    'class' => 'form-control',
                    'id' => 'address-input', // Define the id attribute here
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an address',
                    ])
                ],
            ])
            ->add('phoneNum', TextType::class, [
                'label' => 'Phone Number',
                'attr' => [
                    'placeholder' => 'Enter phone number',
                    'class' => 'form-control',
                    'id' => 'phoneNum-input', // Define the id attribute here
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a phone number',
                    ])
                ],
            ])
            ->add('birthDate',BirthdayType::class, [
                'label' => 'Birth date',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Male' => 'male',
                    'Female' => 'female',
                   
                ],
                'expanded' => true,
                'multiple' => false,
               
            ] )
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Admin' => 'Admin',
                    'Artist' => 'Artist',
                    'Subscriber' => 'Subscriber',
                    'Gallery manager' => 'Gallery manager',
                    'Auction manager' => 'Auction manager',
                    'Events manager' => 'Events manager',
                    'Tickets manager' => 'Tickets manager',
                    'Users manager' => 'Users manager',
                ],
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Choose a role',
            ])
            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
