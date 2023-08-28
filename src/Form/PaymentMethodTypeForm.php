<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

Class PaymentMethodTypeForm extends AbstractType{

public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder
        ->add('paymentMethod', ChoiceType::class, [
            'choices'  => [
                
                'Espèces' => 'Espèces',
                'Chèque' => 'Chèque',
            ],
        ])
        ->add('submit', SubmitType::class);
}

}