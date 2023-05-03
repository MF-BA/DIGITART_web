<?php

namespace App\Form;

use App\Entity\Bid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BidType extends AbstractType
{
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $highestBid = $options['highest_bid'];
            $auctionIncrement = $options['auction_increment'];
            $startingPrice = $options['starting_price'];
        
            $builder
            ->add('offer', null, [
                'attr' => [
                    'id' => 'bidInput',
                    'required' => true,
                ],
                'label' => false,
                'constraints' => [
                    new Assert\GreaterThanOrEqual([
                        'value' => $highestBid > 0 ? $highestBid + $auctionIncrement : $startingPrice,
                        'message' => 'The offer is too low'
                    ])
                ]
            ]);
        
        }
    


public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => Bid::class,
        'highest_bid' => null,
        'auction_increment' => null,
        'starting_price' => null,
    ]);
}   
}
