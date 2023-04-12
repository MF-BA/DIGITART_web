<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Form\Extension\Core\Type\FileType;
class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('eventName', TextType::class, [
            'attr' => [
                'class' => 'form-control',
            ],
        ])
        
        ->add('startDate', BirthdayType::class, [               
            'label' => 'Start date',
            'widget' => 'single_text',
            'attr' => [
                'class' => 'form-control',
  
            ],
            'data' => new \DateTime(),
        ])
        ->add('endDate', BirthdayType::class, [
            'label' => 'End date',
            'widget' => 'single_text',
            'attr' => [
                'class' => 'form-control',
                

            ],

            'data' => new \DateTime(),
        ])
        ->add('nbParticipants', NumberType::class, [
            'label' => 'nbParticipants',
            'attr' => [
                'min' => 0,
                'input' => 'number',
                'pattern' => '\d*', // restrict input to only numbers
            ],

        ])
           
            ->add('detail', TextType::class, [

            ])
            ->add('startTime', NumberType::class, [
                'label' => 'Start time',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 23,
                ],
                
            ])
            ->add('image', FileType::class, [
                'label' => false,
                'multiple' => false,
                'mapped' => false,
                'required' => false
            ])
            ->add('idRoom', EntityType::class, [                 
                'class' => Room::class,                 
                'choice_label' => 'nameRoom',             
            ])
            ->add('images', FileType::class, [
                'label' => false,
                'multiple' => true,
                'mapped' => false,
                'required' => false
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }

}
