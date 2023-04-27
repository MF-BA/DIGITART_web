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
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Config\KarserRecaptcha3Config;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Captcha\Bundle\CaptchaBundle\Validator\Constraints\ValidCaptcha;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaV3Type;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrueV3;

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
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Firstname',
                'attr' => [
                    'placeholder' => 'Enter first name',
                    'class' => 'form-control',
                    'id' => 'firstname-input', // Define the id attribute here
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Lastname',
                'attr' => [
                    'placeholder' => 'Enter last name',
                    'class' => 'form-control',
                    'id' => 'lastname-input', // Define the id attribute here
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
            
        ]);
        $builder->add('password', PasswordType::class, [
            'mapped' => false,
            'label' => 'Password',
            'attr' => [
                'placeholder' => 'Enter Password',
                'class' => 'form-control',
                'id' => 'password-input', // Define the id attribute here
            ],
            'constraints'=> [
                new NotBlank([
                    'message' => 'Please enter a Password',
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
            /*->add('captchaCode', CaptchaType::class, [
                'captchaConfig' => 'ExampleCaptcha',
                'constraints' => [
                    new ValidCaptcha([
                        'message' => 'Invalid captcha, please try again',
                    ]),
                ]
            ])*/
            /*->add('recaptcha', EWZRecaptchaV3Type::class, array(
                'action_name' => 'contact',
                'constraints' => array(
                    new IsTrueV3()
                )
            ));*/
            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
