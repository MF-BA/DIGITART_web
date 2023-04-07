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
class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('eventName', TextType::class, [
            'constraints' => [
                new Length([
                    'min' => '3',
                    'max' => '50',
                    'minMessage' => 'doit etre plus que 3',
                    'maxMessage' => 'doit etre moins que 49',
                ]),
                new NotNull([
                    'message' => 'Event name cannot be empty',
                ]),
            ],
        ])
        ->add('startDate', BirthdayType::class, [               
            'label' => 'Start date',
            'widget' => 'single_text',
            'attr' => [
                'class' => 'form-control',
  
            ],
            'constraints' => [
                new NotNull([
                    'message' => 'Event name cannot be empty',
                ]),
            ],
            'data' => new \DateTime(),
        ])
        ->add('endDate', BirthdayType::class, [
            'label' => 'End date',
            'widget' => 'single_text',
            'attr' => [
                'class' => 'form-control',

            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'End date cannot be empty',
                ]),
            ],
            'data' => new \DateTime(),
        ])
            ->add('nbParticipants')
            ->add('detail', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => '5',
                        'max' => '100',
                        'minMessage' => 'doit etre plus que 5',
                        'maxMessage' => 'doit etre moins que 100',
                    ]),
                    new NotNull([
                        'message' => 'Event details cannot be empty',
                    ]),
                ],
            ])
            ->add('startTime', NumberType::class, [
                'label' => 'Start time',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 23,
                ],
                'constraints' => [
                    new NotNull([
                        'message' => 'Start time cannot be empty',
                    ]),
                    new Range([
                        'min' => 0,
                        'max' => 23,
                        'notInRangeMessage' => 'Start time must be between {{ min }} and {{ max }}',
                    ]),
                ],
            ])
            ->add('image')
            ->add('idRoom', EntityType::class, [                 
                'class' => Room::class,                 
                'choice_label' => 'nameRoom',             
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
