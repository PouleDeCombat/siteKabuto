<?php

namespace App\Form;

use App\Entity\Products;
use App\Entity\ProductSize;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CreateProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Product Name',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Price',
                'required' => true,
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'required' => true,
            ])
            // If you want the user to provide the created_at, uncomment the following line.
            // Otherwise, it's a good practice to set it automatically on persist.
            // ->add('created_at', DateType::class, [
            //     'label' => 'Created At',
            //     'data' => new \DateTime('now'),
            //     'widget' => 'single_text', // This will make it a single input instead of multiple select fields
            //     'empty_data' => new \DateTime('now') 
            // ])
            ->add('images', TextType::class, [
                'label' => 'Image Path',
                'data' => 'images/*nom de votre image*',
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug (SEO-friendly URL)',
                'required' => true,
            ])
            ->add('size', EntityType::class, [
                'class' => ProductSize::class,
                'choice_label' => 'taille',  // Use 'taille' instead of 'name'
                'multiple' => true,
                'expanded' => true,
                'label' => 'Size',
                'required' => false,
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}

