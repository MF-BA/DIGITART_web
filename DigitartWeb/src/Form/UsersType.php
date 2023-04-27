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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


class UsersType extends AbstractType
{
    private $security;
 

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentUser = $this->security->getUser();
        $currentUserId = $currentUser ? $currentUser->getId() : null;
        
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
        ]);
       
    $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($currentUserId) {
        $form = $event->getForm();
        $user = $event->getData();
        $roles = $user->getRoles();
        
        // Check if the form is being used to edit an existing user
        if ($user instanceof Users && $user->getId() !== null && $user->getId() !== $currentUserId) {
            // If so, remove the email and password fields from the form
            $form->remove('email');
            $form->remove('password');
            $form->remove('userImages');
            $form->remove('image');
           
        }
        if ($user instanceof Users && $user->getId() === null) {
           
            $form->remove('userImages');
            $form->remove('image');
        }
        if ($user->getId() === $currentUserId){
            $form->remove('password');
            if (in_array('ROLE_SUBSCRIBER', $roles) || in_array('ROLE_ARTIST', $roles)) {
                $form->remove('role');
                $form->remove('userImages');
                $form->remove('image');
            }
        }
       
    })
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
            'attr' => [
                'class' => 'form-check-inline' // add this line
            ]
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
            'placeholder' => 'Choose a role',
            
        ])
         ->add('userImages', FileType::class, [
           'label' => false,
           'multiple' => true,
           'mapped' => false,
           'required' => false
         ])   
         ->add('image', FileType::class, [
            'label' => false,
            'multiple' => false,
            'mapped' => false,
            'required' => false
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
