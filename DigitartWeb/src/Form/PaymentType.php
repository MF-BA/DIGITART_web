<?php

namespace App\Form;

use App\Entity\Users;
use App\Entity\Payment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('purchaseDate', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Ticket Start Date',
            'attr' => ['min' => (new \DateTime())->format('Y-m-d')],
        ])
        ->add('nbAdult', IntegerType::class, [
            'label' => ' ',
            'data' => '0',
            'attr' => ['min' => 0, 'max' => 10, 'class' => 'adult-spinner'],
        ])
        ->add('nbTeenager', IntegerType::class, [
            'label' => ' ',
            'data' => '0',
            'attr' => ['min' => 0, 'max' => 10, 'class' => 'teen-spinner'],
        ])
        ->add('nbStudent', IntegerType::class, [
            'label' => ' ',
            'data' => '0',
            'attr' => ['min' => 0, 'max' => 10, 'class' => 'student-spinner'],
        ])
        ->add('totalPayment', IntegerType::class, [
            'label' => 'Total Amount',
            'mapped' => true,
            'attr' => [
                'class' => 'total-amount',
                'readonly' => true,
            ],
        ]);
        
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}
