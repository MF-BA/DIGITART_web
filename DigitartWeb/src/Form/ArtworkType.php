<?php

namespace App\Form;

use App\Entity\Artwork;
use App\Entity\Room;
use App\Entity\Users;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType ;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArtworkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('artworkName')
            ->add('ownerType', ChoiceType::class, [
                'choices' => [
                    'Artwork owned by an artist' => 'artist',
                    'Artwork owned by the museum' => 'museum'
                ],
                'data' => $builder->getData()->getOwnerType(),
                'label_attr' => ['class' => 'sr-only']
            ])
            ->add('idArtist', EntityType::class, [
                'class' => Users::class,
                'label' => 'existing artist ',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.role = :role')
                        ->setParameter('role', 'Artist')
                        ->orderBy('u.lastname', 'ASC');
                },
                'choice_label' => 'lastname', // use the artist's last name for display
                'choice_value' => 'id',
                'placeholder' => 'Select an Artist', // optional placeholder text
                'required' => false, // or true, depending on your needs
                'attr' => [ 'style' => 'display:none;',],
               
            ])
            ->add('artistName', TextType::class, [
                'label' => 'Artist name',
                'required' => false,
                
                'mapped' => true, // map this field to the 'artistName' property

            ])
            ->add('dateArt', DateType::class, [
                'widget' => 'single_text',
                'label' => 'artwork date ',
                'attr' => ['max' => (new \DateTime())->format('Y-m-d')],
                
            ])
            ->add('description')

            ->add('images', FileType::class, [
                'label' => false,
                'multiple' => true,
                'mapped' => false,
                'required' => false
            ])
            
            #->addViewTransformer(new StringToFileTransformer())
            ->add('idRoom', EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'nameRoom',
                 'data_class' => null,
            ]);



   // add event listener to dynamically show/hide the appropriate owner field based on user selection
   $builder->get('ownerType')->addEventListener(
    FormEvents::POST_SUBMIT,
    function (FormEvent $event) {
        $form = $event->getForm();
        $ownerType = $form->getData();
        $parentData = $form->getParent()->getData();
        $artwork = $parentData->artwork ?? null; // Get the artwork data, or set it to null if it doesn't exist
        
        if ($ownerType === 'artist') {
            $form->getParent()->add('idArtist', EntityType::class, [
                'class' => Users::class,
                'label' => 'existing artist ',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.role = :role')
                        ->setParameter('role', 'Artist')
                        ->orderBy('u.lastname', 'ASC');
                },
                'choice_label' => 'lastname', // use the artist's last name for display
                'choice_value' => 'id',
                'placeholder' => 'Select an Artist', // optional placeholder text
                'required' => false, // or true, depending on your needs
                'attr' => ['style' => 'display:none;',],
                'data' => ($artwork && $artwork->getIdArtist()) ? $artwork->getIdArtist() : null, // pre-select the artist if editing an existing artwork
            ]);
        } else if ($ownerType === 'museum') {
            $form->getParent()->add('artistName', TextType::class, [
                'label' => 'Artist name',
                'required' => false,
                'mapped' => true, // map this field to the 'artistName' property
                'attr' => ['style' => 'display:none;',],
                'data' => ($artwork && $artwork->getArtistName()) ? $artwork->getArtistName() : null, // pre-populate the artist name if editing an existing artwork
            ]);
        }
    }
);
}


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artwork::class,
        ]);
    }
}
