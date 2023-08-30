<?php

namespace App\Form;

use App\Entity\Products;
use App\Entity\ProductSize;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditProductTypeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrez le nom du produit'
                ]
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Entrez la description du produit'
                ]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix',
                'attr' => [
                    'placeholder' => 'Entrez le prix du produit'
                ]
            ])
            ->add('stock', NumberType::class, [
                'label' => 'Stock',
                'attr' => [
                    'placeholder' => 'Entrez la quantité en stock'
                ]
            ])
            ->add('images', TextType::class, [
                'label' => 'Images (URL)',
                'attr' => [
                    'placeholder' => 'Entrez l\'URL de l\'image du produit'
                ]
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'attr' => [
                    'placeholder' => 'Entrez le slug (identifiant unique pour l\'URL)'
                ]
            ])
            ->add('size', EntityType::class, [
                'label' => 'Tailles',
                'class' => ProductSize::class,
                'choice_label' => 'taille', // Suppose que votre entité ProductSize a un champ "name"
                'multiple' => true,
                'expanded' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}

