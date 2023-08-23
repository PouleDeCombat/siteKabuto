<?php

namespace App\Form;

use App\Entity\Orders;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class OrderPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isPayer', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false
                ],
                'expanded' => true,
                'label' => 'Payé'
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'choices' => [
                    'Espèces' => 'espece',
                    'Chèque' => 'cheque'
                ],
                'expanded' => true,
                'label' => 'Méthode de paiement'
            ])
            ->add('isProcessed', ChoiceType::class, [
                'choices' => [
                    'Traitée' => true,
                    'Non traitée' => false
                ],
                'expanded' => true,
                'label' => 'Traitement de la commande'
            ])
            ->add('save', SubmitType::class, ['label' => 'Mettre à jour']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Orders::class,
        ]);
    }
}
