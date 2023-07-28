<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserAdminEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'border-0 border-bottom mb-5 text-black',
                    'style' => 'border-bottom: 1px solid grey; background-color: transparent; width: 90%;text-transform: uppercase;']
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'border-0 border-bottom mb-5 text-black',
                    'style' => 'border-bottom: 1px solid grey; background-color: transparent; width: 90%;text-transform: uppercase;'
                ]
            ])
            
            ->add('prenom', TextType::class, [
                'attr' => [
                    'class' => 'border-0 border-bottom mb-5 text-black',
                    'style' => 'border-bottom: 1px solid grey; background-color: transparent; width: 90%;text-transform: uppercase;'
                   ]
            ])
            ->add('adresse', TextType::class, [
                'attr' => [
                    'class' => 'border-0 border-bottom mb-5 text-black',
                    'style' => 'border-bottom: 1px solid grey; background-color: transparent; width: 90%;text-transform: uppercase;']
            ])
            ->add('zipcode', TextType::class, [
                'attr' => [
                    'class' => 'border-0 border-bottom mb-5 text-black',
                    'style' => 'border-bottom: 1px solid grey; background-color: transparent; width: 90%;'],
                   'label' => 'Code postal'
            ])
            ->add('ville', TextType::class, [
                'attr' => [
                    'class' => 'border-0 border-bottom mb-5 text-black',
                    'style' => 'border-bottom: 1px solid grey; background-color: transparent; width: 90%;text-transform: uppercase;']
            ])
            ->add('telephone', TextType::class, [
                'attr' => [
                    'class' => 'border-0 border-bottom mb-5 text-black',
                    'style' => 'border-bottom: 1px solid grey; background-color: transparent; width: 90%;'],
                   'label' => 'Numéro de téléphone'
            ])
            ->add('profession', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'border-0 border-bottom mb-5 text-black',
                    'style' => 'border-bottom: 1px solid grey; background-color: transparent; width: 90%;text-transform: uppercase;']
            ])
            ->add('date_de_naissance', DateType::class, [
                'format' => 'dd-MM-yyyy',
                'years' => range(date('Y') - 90, date('Y')),
                'attr' => [
                   'class' => 'pt-1 '],
                   'label' => 'Date de naissance'
            ])
            ->add('lieu_de_naissance', TextType::class, [      
                'attr' => [
                    'class' => 'border-0 border-bottom text-black',
                    'style' => 'border-bottom: 1px solid grey; background-color: transparent; width: 90%;text-transform: uppercase;'],
                   'label' => 'Lieu de naissance( Code Postal )'
                ]);
            // ->add('created_at')

           
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
