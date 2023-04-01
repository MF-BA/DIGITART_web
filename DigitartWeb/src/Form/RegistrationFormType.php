<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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

class RegistrationFormType extends AbstractType
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
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a cin',
                    ]),
                    new Length([
                        'min' => 8,
                        'max' => 8,
                        'exactMessage' => 'Cin must contain exactly {{ limit }} digits',
                    ]),
                    new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Cin must be a number',
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
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z]+$/',
                        'message' => 'Please enter a valid first name',
                    ]),
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
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z]+$/',
                        'message' => 'Please enter a valid last name',
                    ]),
                ],
            ]);
             // Add the email and password fields as hidden fields
        // so they are still submitted with the form
        $builder->add('email',TextType::class, [
            'label' => 'Email',
            'attr' => [
                'placeholder' => 'Enter Email',
                'class' => 'form-control',
                'id' => 'email-input', // Define the id attribute here
            ],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Please enter an email',
                ]),
                new Assert\Email([
                    'message' => 'Please enter a valid email address',
                ]),
            ],
        ]);
        $builder->add('plainPassword', PasswordType::class, [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter a password',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Your password should be at least {{ limit }} characters',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
                new Regex([
                    'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#\$%\^&\*\(\)]).+$/',
                    'message' => 'Password should contain at least one uppercase letter, one lowercase letter, and one special character (!@#$%^&*())',
                ]),
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
            ->add('phoneNum', IntegerType::class, [
                'label' => 'Phone Number',
                'attr' => [
                    'placeholder' => 'Enter phone number',
                    'class' => 'form-control',
                    'id' => 'phoneNum-input', // Define the id attribute here
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a phone number',
                    ]),
                    
                    new Length([
                    'min' => 8,
                    'max' => 8,
                    'exactMessage' => 'Phone number must contain exactly {{ limit }} digits',
                     ]),
                     new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Phone number must be a number',
                    ]),
                    
                ],
            ])
            ->add('birthDate',BirthdayType::class, [
                'label' => 'Birth date',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
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
            'data_class' => Users::class,
        ]);
    }
}
