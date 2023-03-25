<?php

namespace App\Form;

use App\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;


class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ticketDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Ticket Start Date ',
                'attr' => ['min' => (new \DateTime())->format('Y-m-d')],
            ])
            ->add('ticketEDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Ticket End Date',
                'attr' => ['min' => (new \DateTime())->format('Y-m-d')],
                'constraints' => [
                    new GreaterThanOrEqual([
                        'propertyPath' => 'parent.all[ticketDate].data',
                        'message' => 'The end date must be greater than the start date.'
                    ])
                ]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Price',
                'attr' => [
                    'min' => 0,
                    'input' => 'number',
                    'pattern' => '\d*', // restrict input to only numbers
                ],
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'The price should be greater than 0.'
                    ])
                ]
            ])
            
            
            ->add('ticketType', ChoiceType::class, [
                'label' => 'Ticket Type',
                'choices' => [
                    'Student' => 'Student',
                    'Teen' => 'Teen',
                    'Adult' => 'Adult'
                ]
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
