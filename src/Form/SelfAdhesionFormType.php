<?php
namespace App\Form;

use App\Entity\Abonnements;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelfAdhesionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('abonnement', EntityType::class, [
                'label' => 'Choisir un abonnement',
                'class' => Abonnements::class,
                'choice_label' => function (Abonnements $abonnement) {
                    return sprintf('%s - %s - %s - %s€', $abonnement->getCategorie(), $abonnement->getDiscipline(), $abonnement->getDurée(), $abonnement->getPrix());
                },
                'required' => true,
                'placeholder' => 'Sélectionnez un abonnement',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.categorie = :categorie')
                        ->setParameter('categorie', 'adultes');
                },
            ]);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // no need for a data class, as the form only represents a choice
        ]);
    }
}
