<?php

namespace App\Controller;

use App\Entity\Kids;
use App\Entity\Adhesions;
use App\Entity\Abonnements;
use App\Repository\AbonnementsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class AbonnementsController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/profile/StatutAbonnement", name: "app_statut_adhesion")]
    public function showSubscriptionStatus(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            // Redirect to login if user is not authenticated
            return $this->redirectToRoute('app_login');
        }

        $adhesionsRepo = $em->getRepository(Adhesions::class);

        $kidsRepo = $this->entityManager->getRepository(Kids::class);

        $subscriptions = $em->getRepository(Adhesions::class)->findBy(['user' => $user]);

        $kidsSubscriptions = [];
        $kids = $em->getRepository(Kids::class)->findBy(['user' => $user]);
    foreach ($kids as $kid) {
    $kidAdhesions = $kid->getAdhesions(); // Use getAdhesions() for the Kid entity
    $kidsSubscriptions[] = [
        'kid' => $kid,
        'adhesions' => $kidAdhesions,
    ];
}


        return $this->render('usersPages/statutAdhesion.html.twig', [
            'subscriptions' => $subscriptions,
            'kidsSubscriptions' => $kidsSubscriptions,
        ]);
    }
}

