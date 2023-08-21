<?php

namespace App\Form;

use App\Entity\Kids;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EditKidsCompetiteurProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
      
            
        ->add('categoriePoid', ChoiceType::class, [
            'choices' => [
                'PRE - MIRIM (4 à 6 ans)' => [
                    'Galo: -16.5 kg' => 'Galo -16.5 kg',
                    'Pluma: -19 kg' => 'Pluma -19 kg',
                    'Pena: -22 kg' => 'Pena -22 kg',
                    'Leve: -25 kg' => 'Leve -25 kg',
                    'Medio: -28 kg' => 'Medio -28 kg',
                    'Meio Pesado: -31 kg' => 'Meio Pesado -31 kg',
                    'Pesado: -34 kg' => 'Pesado -34 kg',
                    'Super Pesado: -34 kg' => 'Super Pesado -34 kg',
                    'Pesadissimo: +37 kg' => 'Pesadissimo +37 kg',
                ],
                'MIRIM (7 à 9 ans)' => [
                    'Galo: -23.5 kg' => 'Galo -23.5 kg',
                    'Pluma: -27 kg' => 'Pluma -27 kg',
                    'Pena: -30 kg' => 'Pena -30 kg',
                    'Leve: -33 kg' => 'Leve -33 kg',
                    'Medio: -36 kg' => 'Medio -36 kg',
                    'Meio Pesado: -39 kg' => 'Meio Pesado -39 kg',
                    'Pesado: -42 kg' => 'Pesado -42 kg',
                    'Super Pesado: -45 kg' => 'Super Pesado -45 kg',
                    'Pesadissimo: +45 kg' => 'Pesadissimo +45 kg',
                ],
                'INFANTIL (10 à 12 ans)' => [
                    'Galo: -30 kg' => 'Galo -30 kg',
                    'Pluma: -34 kg' => 'Pluma -34 kg',
                    'Pena: -38 kg' => 'Pena -38 kg',
                    'Leve: -42 kg' => 'Leve -42 kg',
                    'Medio: -46 kg' => 'Medio -46 kg',
                    'Meio Pesado: -50 kg' => 'Meio Pesado -50 kg',
                    'Pesado: -54 kg' => 'Pesado -54 kg',
                    'Super Pesado: -58 kg' => 'Super Pesado -58 kg',
                    'Pesadissimo: +58 kg' => 'Pesadissimo +58 kg',
                ],
                'INFANTIL JUVENIL (13 à 15 ans)' => [
                    'Galo: -40 kg' => 'Galo -40 kg',
                    'Pluma: -44 kg' => 'Pluma -44 kg',
                    'Pena: -48 kg' => 'Pena -48 kg',
                    'Leve: -52 kg' => 'Leve -52 kg',
                    'Medio: -56 kg' => 'Medio -56 kg',
                    'Meio Pesado: -60 kg' => 'Meio Pesado -60 kg',
                    'Pesado: -64 kg' => 'Pesado -64 kg',
                    'Super Pesado: -68 kg' => 'Super Pesado -68 kg',
                    'Pesadissimo: +68 kg' => 'Pesadissimo +68 kg',
                ],
                'JUVENIL MASCULIN (16 à 17 ans)' => [
                    'Galo: -53.5 Kg' => 'Galo -53.5 kg',
                    'Pluma: -58.5 kg' => 'Pluma -58.5 kg',
                    'Pena: -64 kg' => 'Pena -64 kg',
                    'Leve: -69 kg' => 'Leve -69 kg',
                    'Medio: -74 kg' => 'Medio -74 kg',
                    'Meio Pesado: -79.3 kg' => 'Meio Pesado -79.3 kg',
                    'Pesado: -84.3 kg' => 'Pesado -84.3 kg',
                    'Super Pesado: -89.3 kg' => 'Super Pesado -89.3 kg',
                    'Pesadissimo: +89.3 kg' => 'Pesadissimo +89.3 kg',
                ],
                'JUVENIL FEMMININ (16 à 17 ans)' => [
                    'Galo: -44.3 kg' => 'Galo -44.3 kg',
                    'Pluma: -48.3 kg' => 'Pluma -48.3 kg',
                    'Pena: -52.5 kg' => 'Pena -52.5 kg',
                    'Leve: -56.5 kg' => 'Leve -56.5 kg',
                    'Medio: -60.5 kg' => 'Medio -60.5 kg',
                    'Meio Pesado: -65 kg' => 'Meio Pesado -65 kg',
                    'Pesado: -69 kg' => 'Pesado -69 kg',
                    'Super Pesado: -73 kg' => 'Super Pesado -73 kg',
                    'Pesadissimo: +73 kg' => 'Pesadissimo +73 kg',
                ],
            ],   
        ])
        ->add('ceinture', ChoiceType::class, [
            'choices' => [
                'Blanche' => 'Blanche',
                'Grise' => 'Grise',
                'Jaune' => 'Jaune',
                'Orange' => 'Orange',
                'Verte' => 'Verte',
                'Bleue' => 'Bleue',
            ],
        ])
        ->add('trancheAge', ChoiceType::class, [
            'choices' => [
                'Pre-Mirim' => 'Pre-Mirim',
                'Mirim' => 'Mirim',
                'Infantil' => 'Infantil',
            ],
        ]) ;
            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Kids::class,
        ]);
    }
}
