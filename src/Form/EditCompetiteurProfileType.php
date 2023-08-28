<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EditCompetiteurProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('categoriePoid', ChoiceType::class, [
                'choices' => [
                    
                    'NoGi Homme Pluma -61,50kg' =>'NoGi Homme Pluma -61,50kg',
                    'NoGi Homme Pena -67,50kg' =>'NoGi Homme Pena -67,50kg',
                    'NoGi Homme Leve -73,50kg' =>'NoGi Homme Leve -73,50kg',
                    'NoGi Homme Medio -79,80kg' =>'NoGi Homme Medio -79,80kg',
                    'NoGi Homme Meio Pesado -85,30kg' =>'NoGi Homme Meio Pesado -85,30kg',
                    'NoGi Homme Pesado -91,30kg' =>'NoGi Homme Pesado -91,30kg',
                    'NoGi Homme Super Pesado -97,50kg' =>'NoGi Homme Super Pesado -97,50kg',
                    'NoGi Homme Pesadissimo +97,50kg' =>'NoGi Homme Pesadissimo +97,50kg',
                    'NoGi Femme Galo -46,50kg' => 'NoGi Femme Galo -46,50kg',
                    'NoGi Femme Pluma -51,50kg' => 'NoGi Femme Pluma -51,50kg',
                    'NoGi Femme Pena -56,50kg' => 'NoGi Femme Pena -56,50kg',
                    'NoGi Femme Leve -51,50kg' => 'NoGi Femme Leve -51,50kg',
                    'NoGi Femme Medio -66,50kg' => 'NoGi Femme Medio -66,50kg',
                    'NoGi Femme Meio Pesado -71,50kg' => 'NoGi Femme Meio Pesado -71,50kg',
                    'NoGi Femme Pesado -76,50kg' => 'NoGi Femme Pesado -76,50kg',
                    'NoGi Femme Super Pesado +76,50kg' => 'NoGi Femme Super Pesado +76,50kg',
                    
                ],
                'label' => 'Categorie Poid NoGi'
                
                
            ])
            ->add('categoriePoidGI', ChoiceType::class, [
                'choices' => [
                    'GI Homme Galo -57.50kg' => 'GI Galo -57.50kg',
                    'GI Homme Pluma -64kg' => 'GI Homme Pluma -64kg',
                    'GI Homme Pena -70kg' => 'GI Homme Pena -70kg',
                    'GI Homme Leve -76kg' => 'GI Homme Leve -76kg',
                    'GI Homme Medio -82,30kg' => 'GI Homme Medio -82,30kg',
                    'GI Homme Meio Pesado -88,30kg' => 'GI Homme Meio Pesado -88,30kg',
                    'GI Homme Pesado -94,30kg' => 'GI Homme Pesado -94,30kg',
                    'GI Homme Super Pesado -100,50kg' => 'GI Homme Super Pesado -100,50kg',
                    'GI Homme Pessadissimo +100,50kg' => 'GI Homme Pessadissimo +100,50kg',
                    'GI Femme Galo -48.50kg' => 'GI Femme Galo -48.50kg',
                    'GI Femme Pluma -53.50kg' => 'GI Femme Pluma -53.50kg',
                    'GI Femme Pena -58,50kg' => 'GI Femme Pena -58,50kg',
                    'GI Femme Leve -64kg' => 'GI Femme Leve -64kg',
                    'GI Femme Medio -69kg' => 'GI Femme Medio -69kg',
                    'GI Femme Meio Pesado -74kg' => 'GI Femme Meio Pesado -74kg',
                    'GI Femme Pesado -79,30kg' => 'GI Femme Pesado -79,30kg',
                    'GI Femme Super Pesado +79,30kg' => 'GI Femme Super Pesado +79,30kg',
                    
                    
                ],
                'label' => 'Categorie Poid Gi'
                
                
            ])


            
            ->add('ceinture', ChoiceType::class, [
                'choices' => [
                    'Blanche' => 'Blanche',
                    'Bleue' => 'Bleue',
                    'Violet' => 'Violet',
                    'Marron' => 'Marron',
                    'Noire' => 'Noire',
                ],
            ])
            ->add('kimono', ChoiceType::class, [
                'choices' => [
                    'Gi' => 'Gi',
                    'NoGi' => 'NoGi',
                    'Gi et NoGi' => 'Gi et NoGi',
                ],
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
