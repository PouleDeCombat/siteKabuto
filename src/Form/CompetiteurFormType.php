<?php

namespace App\Form;

use App\Entity\Competiteurs;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CompetiteurFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('ceinture', ChoiceType::class, [
            'choices' => [
                'Blanche' => 'Blanche',
                'Bleue' => 'Bleue',
                'Violet' => 'Violet',
                'Marron' => 'Marron',
                'Noire' => 'Noire',
            ],
        ])
        ->add('categoriePoid', ChoiceType::class, [
            'choices' => [
                'GI Homme -55kg' => 'Gi Homme -55kg',
                'GI Homme -65kg' => 'Gi Homme -65kg',
                // ajoutez vos autres choix ici
            ],
            'multiple' => true,
            'expanded' => true,
            'attr' => [
                'class' => 'form-check-input',
                'onchange' => 'limitCheckboxSelection(this, 2)',
            ],
            'label_attr' => [
                'class' => 'form-check-label',
            ],
        ])
        

        ->add('kimono', ChoiceType::class, [
            'choices' => [
                'Gi' => 'Gi',
                'NoGi' => 'NoGi',
                'Gi et NoGi' => 'Gi et NoGi',
            ],
        ])
        ->add('user', EntityType::class, [
            'class' => Users::class,
            'choice_label' => 'nom', // ou tout autre champ de l'entité Users que vous voulez afficher
        ])
        ->add('userId', HiddenType::class, [
            'mapped' => false, 
        ]);

        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competiteurs::class,
        ]);
    }

    
}
