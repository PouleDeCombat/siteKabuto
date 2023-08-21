<?php
namespace App\Form;

use App\Form\KidsAdhesionFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class KidsAdhesionCollectionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('userAbonnement', SelfAdhesionFormType::class, [
            'label' => false
        ])
            ->add('kidsAbonnement', CollectionType::class, [
                'entry_type' => KidsAdhesionFormType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false,
            ]);
    }
}
