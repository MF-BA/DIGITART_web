<?php

namespace App\Form;

use App\Entity\Bid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BidType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
    ->add('offer', null, [
        'attr' => [
            'id' => 'bidInput',
            'required' => true,
        ],
        'label' => false,
    ]);

}


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bid::class,
        ]);
    }
}
