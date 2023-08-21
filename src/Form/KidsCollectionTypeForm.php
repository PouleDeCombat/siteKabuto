<?php
namespace App\Form;

use App\Model\KidsCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class KidsCollectionTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('kids', CollectionType::class, [
                'entry_type' => KidsTypeForm::class, // le formulaire pour un seul enfant
                'allow_add' => true, // permettre d'ajouter plusieurs enfants
                'by_reference' => false, // autoriser la mise à jour de l'objet parent
                'label' => 'Enfants', // libellé pour ce champ
                'entry_options' => ['label' => false], // supprimer le libellé pour chaque entrée
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => KidsCollection::class,
        ]);
    }
}
