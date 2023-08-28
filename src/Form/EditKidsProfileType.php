<?php

namespace App\Form;

use App\Entity\Kids;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EditKidsProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                   'class' => 'form-control']
                ])
            ->add('prenom', TextType::class, [
                'attr' => [
                   'class' => 'form-control']
            ])
            ->add('adresse', TextType::class, [
                'attr' => [
                   'class' => 'form-control']
            ])
            ->add('zipcode', TextType::class, [
                'attr' => [
                   'class' => 'form-control']
            ])
            ->add('ville', TextType::class, [
                'attr' => [
                   'class' => 'form-control']
            ])
            
            ->add('telephone', TextType::class, [
                'attr' => [
                   'class' => 'form-control']
            ])
            ->add('Valider', SubmitType::class,  [
                'attr' => [
                   'class' => 'mt-2 btn btn-primary']
            ])
           
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Kids::class,
        ]);
    }
}
