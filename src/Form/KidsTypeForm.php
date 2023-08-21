<?php

namespace App\Form;

use App\Entity\Kids;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class KidsTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('nom', TextType::class, [
                'attr' => [
                   'class' => 'form-control'],
                   'label_attr' => ['class' => 'label-white'],
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                   'class' => 'form-control'],
                   'label_attr' => ['class' => 'label-white'],
            ])
            ->add('adresse', TextType::class, [
                'attr' => [
                   'class' => 'form-control'],
                   'label_attr' => ['class' => 'label-white'],
            ])
            ->add('zipcode', TextType::class, [
                'attr' => [
                   'class' => 'form-control'],
                   'label_attr' => ['class' => 'label-white'],
            ])
            ->add('ville', TextType::class, [
                'attr' => [
                   'class' => 'form-control'],
                   'label_attr' => ['class' => 'label-white'],
            ])
            
            ->add('telephone', TextType::class, [
                'attr' => [
                   'class' => 'form-control'],
                   'label_attr' => ['class' => 'label-white'],
            ])
            ->add('date_de_naissance', DateType::class, [
                'format' => 'dd-MM-yyyy',
                'years' => range(date('Y') - 90, date('Y')),
                'attr' => [
                   'class' => 'pt-1 '],
                   'label' => 'Date de naissance',
                   'label_attr' => ['class' => 'label-white'],
            ])
            ->add('lieu_de_naissance', TextType::class, [
                'attr' => [
                   'class' => 'form-control'],
                   'label_attr' => ['class' => 'label-white'],
            ])
            // ->add('save', SubmitType::class, [
            //     'attr' => [
            //         'class' => 'btn mt-3 btn-secondary'
            //     ],
            //     'label' => 'Valider'
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Kids::class,
        ]);
    }
}
