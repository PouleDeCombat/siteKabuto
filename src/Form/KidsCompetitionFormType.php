<?php

namespace App\Form;

use App\Entity\KidsCompetitions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class KidsCompetitionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'label' => 'Nom de la competition',
                'attr' => [
                'class' => 'border-0 border-bottom mb-5 text-black',
                'style' => 'border-bottom: 1px solid grey; background-color: transparent; width: 90%;text-transform: uppercase;'
            ]
        ])
        ->add('location', TextType::class ,[
            'label' => 'Location',
            'attr' => [
                'class' => 'border-0 border-bottom mb-5 text-black',
                'style' => 'border-bottom: 1px solid grey; background-color: transparent; width: 90%;text-transform: uppercase;'
            ]
        ])
        ->add('startDate', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de début',
            'attr' => [
                'class' => 'border-0 border-bottom mb-5 text-black text-center',
            ],
        ])
        ->add('endDate', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de fin',
            'attr' => [
                'class' => 'border-0 border-bottom mb-5 text-black text-center',
            ],
        ])
        // ->add('Valider', SubmitType::class,  [
        //     'attr' => [
        //        'class' => 'form-control']
        // ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => KidsCompetitions::class,
        ]);
    }
}
