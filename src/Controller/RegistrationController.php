<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Kids;
use App\Entity\Users;
use App\Form\KidsTypeForm;
use App\Entity\Abonnements;
use App\Model\KidsCollection;
use App\Form\KidsAdhesionFormType;
use App\Form\RegistrationFormType;
use App\Form\SelfAdhesionFormType;
use App\Repository\KidsRepository;
use App\Repository\UsersRepository;
use App\Form\KidsCollectionTypeForm;
use App\Security\UsersAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AbonnementsRepository;

use App\Form\KidsAdhesionCollectionFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UsersAuthenticator $authenticator, EntityManagerInterface $entityManager, Security $security): Response
    {
        if ($security->getUser()) {
            // Redirige vers la deuxième étape
            return $this->redirectToRoute('app_step_deux');
        }    
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
    
            $entityManager->persist($user);
            $entityManager->flush();
            
            // Authentifiez l'utilisateur si nécessaire
            $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
    
            // Redirige vers la deuxième étape
            return $this->redirectToRoute('app_step_deux');
        }
    
        // Si le formulaire n'est pas soumis ou n'est pas valide, affichez le formulaire
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/addkids', name: 'app_add_kids')]
public function addKid(Request $request, EntityManagerInterface $entityManager): Response
{
    $kid = new Kids();

    $user = $this->getUser();
    
    $form = $this->createForm(KidsTypeForm::class, $kid);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $kid->setUser($user);
        $entityManager->persist($kid);
        $entityManager->flush();

        return $this->redirectToRoute('app_profile');
    }

    return $this->render('registration/kidsregister.html.twig', [
        'kidsForm' => $form->createView(),
    ]);
}



#[Route('/register/etapes-deux', name: 'app_step_deux', methods: ["GET"])]
public function showSubscriptionChoice(): Response
{
    return $this->render('registration/registerStep2.html.twig');
}



#[Route('/register/etapes-deux', name: 'app_step_deux_submit', methods: ["POST"])]
public function chooseSubscription(Request $request): Response
{
    $subscriptionType = $request->request->get('subscriptionType');

    switch ($subscriptionType) {
        case 'self':
            return $this->redirectToRoute('app_self_subscriptionForm');
        case 'kids':
            return $this->redirectToRoute('app_kids_subscriptionForm');
        case 'both':
            return $this->redirectToRoute('app_both_subscriptionForm');
        default:
            // Gérer une erreur si aucune option n'est sélectionnée
            $this->addFlash('error', 'Veuillez choisir une option d\'abonnement.');
            return $this->redirectToRoute('app_step_deux');
    }
}

#[Route('/register/etapes-trois', name: 'app_self_subscriptionForm', methods: ["GET"])]
public function showSelfSubscriptionForm(EntityManagerInterface $entityManager): Response
{
   
    // Récupérer la liste des abonnements pour la catégorie adulte
    $abonnements = $entityManager->getRepository(Abonnements::class)->findBy(['categorie' => 'adultes']);

    // Grouper les abonnements par discipline
    $groupedAbonnements = [];
    foreach ($abonnements as $abonnement) {
        $groupedAbonnements[$abonnement->getDiscipline()][] = $abonnement;
    }

    // Créer le formulaire
    $form = $this->createForm(SelfAdhesionFormType::class);
    
    return $this->render('registration/registerStep3.html.twig', [
        'form' => $form->createView(),
        'abonnements' => $groupedAbonnements // Passer les abonnements groupés à la vue
    ]);
}




#[Route('/register/etapes-trois', name: 'app_self_subscriptionForm_submit', methods: ["POST"])]
public function chooseSelfSubscription(Request $request, SessionInterface $session): Response
{
    // Récupérer l'ID de l'abonnement choisi à partir de la requête
    $abonnementId = $request->request->get('abonnement');
    
    if (!$abonnementId) {
        $this->addFlash('error', 'Veuillez choisir un abonnement.');
        return $this->redirectToRoute('app_self_subscriptionForm');
    }

    // Enregistrer l'ID de l'abonnement dans la session
    $session->set('selected_abonnement_id', $abonnementId);

    // Rediriger vers l'étape suivante
    return $this->redirectToRoute('app_step_quatre');
}



#[Route('/register/etapes-quatre', name: 'app_step_quatre', methods: ["GET"])]
public function checkoutStripe(SessionInterface $session, AbonnementsRepository $abonnementsRepository): Response
{
    // Récupérer l'ID de l'abonnement sélectionné (à partir de la session ou d'une autre méthode)
    $abonnementId = $session->get('selected_abonnement_id');

    // Récupérer les détails de l'abonnement de la base de données
    $abonnement = $abonnementsRepository->find($abonnementId);

    // Calculer le total (dans cet exemple, c'est simplement le prix de l'abonnement)
    $total = $abonnement->getPrix();

    // Transmettre les données à la vue
    return $this->render('stripe/checkout_stripe.html.twig', [
        'abonnement' => $abonnement,
        'total' => $total,
        'stripe_key' =>  $_ENV["STRIPE_KEY"] // Clé publiable Stripe
    ]);
}







#[Route('/inscription/etape-trois', name: 'app_kids_subscriptionForm', methods: ["GET", "POST"])]

public function registerKidsStep3(Request $request, EntityManagerInterface $em, KidsRepository $kidsRepo)
{
    $user = $this->getUser();
    $kidsAlreadyAdded = $kidsRepo->findBy(['user' => $user]);

    $kidsCollection = new KidsCollection();
    $form = $this->createForm(KidsCollectionTypeForm::class, $kidsCollection);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $user = $this->getUser();
        foreach ($kidsCollection->getKids() as $kid) {
            $kid->setUser($user);
            $em->persist($kid);
        }
        $em->flush();

        return $this->redirectToRoute('register_kids_step_quatre');
    }

    return $this->render('registration/registerKidsStep3.html.twig', [
        'form' => $form->createView(),
        'kidsAlreadyAdded' => $kidsAlreadyAdded
    ]);
}



    #[Route('/register/etapes-quatre-enfant', name: 'app_kids_step_quatre', methods: ["GET", "POST"])]
    public function registerKidsStep4(SessionInterface $session, KidsRepository $kidsRepository, AbonnementsRepository $abonnementsRepository): Response
    {
        // Vous pouvez récupérer l'utilisateur connecté (assurez-vous que l'utilisateur soit connecté)
        $user = $this->getUser();

        // Récupérez les enfants associés à l'utilisateur connecté
        $kids = $kidsRepository->findBy(['parent' => $user]);

        // Récupérez les abonnements depuis la base de données
        $abonnements = $abonnementsRepository->findAll();

        return $this->render('registerKidsStep4.html.twig', [
            'kids' => $kids,
            'abonnements' => $abonnements,
        ]);
    }


    #[Route('/register/kids/step4', name: 'register_kids_step_quatre')]
    public function showKidsForm(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, AbonnementsRepository $abonnementsRepository): Response
    {
        
        // Récupération des enfants associés à l'utilisateur
        $kidsData = [];
        foreach ($this->getUser()->getKids() as $kid) {
            $kidsData[] = ['abonnement' => null];
        }
    
        // Création du formulaire avec les données
        $form = $this->createForm(KidsAdhesionCollectionFormType::class, ['kidsAbonnement' => $kidsData]);
        if ($request->isMethod('POST')) {
            $abonnements = [];
            $totalPrice = 0;
    
            foreach ($request->request->all() as $key => $value) {
                if (strpos($key, 'abonnement_for_kid_') === 0 || $key === 'abonnement') {
                    $abonnementId = $value;
    
                    $abonnements[] = $abonnementId;
                    $abonnement = $abonnementsRepository->find($abonnementId);
    
                    if ($abonnement) {
                        $totalPrice += (float) $abonnement->getPrix();
                    }
                }
            }

            $session->set('abos', $abonnements);
            $session->set('totalPriceBoth', $totalPrice);

            return $this->redirectToRoute('app_kids_step_cinq');
        }

        $abonnements = $entityManager->getRepository(Abonnements::class)->createQueryBuilder('a')
        ->where('a.categorie = :kids OR a.categorie = :ados')
        ->setParameters([
            'kids' => 'kids',
            'ados' => 'ados'
        ])
        ->getQuery()
        ->getResult();

        $groupedAbonnements = [];
        foreach ($abonnements as $abonnement) {
            $groupedAbonnements[$abonnement->getDiscipline()][] = $abonnement;
        }
        // Affichage de la vue
        return $this->render('registration/registerKidsStep4.html.twig', [
            'form' => $form->createView(),
            'kids' => $this->getUser()->getKids(),
             'groupedAbonnements' => $groupedAbonnements,
           
        ]);
    }
    

    #[Route('/register/etapes-cinq', name: 'app_kids_step_cinq', methods: ["GET"])]
    public function checkoutStripeKids(SessionInterface $session, AbonnementsRepository $abonnementsRepository): Response
    {
         // Récupérer la liste des IDs d'abonnements sélectionnés (à partir de la session ou d'une autre méthode)
    $selectedAboIds = $session->get('abos');
    $totalPrice = $session->get('totalPriceBoth');

    // Récupérer les objets Abonnements à partir des IDs
    $selectedAbonnements = [];
    foreach ($selectedAboIds as $kidIndex => $abonnementId) {
        $abonnement = $abonnementsRepository->find($abonnementId);
        if ($abonnement) {
            $selectedAbonnements[$kidIndex] = $abonnement;
        }
    }

        // Transmettre les données à la vue
        return $this->render('stripe/kidsCheckout_stripe.html.twig', [
            'selectedAbonnements' => $selectedAbonnements,
            'total' => $totalPrice,
            'stripe_key' => $_ENV["STRIPE_KEY"] // Clé publiable Stripe
        ]);
    }




    #[Route('/inscription/etape-trois-bis', name: 'app_both_subscriptionForm', methods: ["GET", "POST"])]

public function registerBothStep3(Request $request, EntityManagerInterface $em, KidsRepository $kidsRepo)
{
    $user = $this->getUser();
    $kidsAlreadyAdded = $kidsRepo->findBy(['user' => $user]);
    $kidsCollection = new KidsCollection();
    $form = $this->createForm(KidsCollectionTypeForm::class, $kidsCollection);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $user = $this->getUser();
        foreach ($kidsCollection->getKids() as $kid) {
            $kid->setUser($user);
            $em->persist($kid);
        }
        $em->flush();

        return $this->redirectToRoute('app_register_both_step_quatre');
    }

    return $this->render('registration/registerBothStep3.html.twig', [
        'form' => $form->createView(),
        'kidsAlreadyAdded' => $kidsAlreadyAdded
    ]);
}



#[Route('/inscription/etape-quatre', name: 'app_register_both_step_quatre')]
public function showBothForm(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
{
    // Récupération des enfants associés à l'utilisateur
    $kidsData = [];
    foreach ($this->getUser()->getKids() as $kid) {
        $kidsData[] = ['abonnement' => null];
    }

    $form = $this->createForm(KidsAdhesionCollectionFormType::class, ['kidsAbonnement' => $kidsData]);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $formData = $form->getData();

        $selectedUserAbonnement = $formData['userAbonnement']['abonnement'];
        $session->set('selected_user_abonnement_id', $selectedUserAbonnement->getId());

        $selectedAbonnements = $formData['kidsAbonnement'];

        $ids = [];
        foreach ($selectedAbonnements as $kidAbonnement) {
            $ids[] = $kidAbonnement['abonnement']->getId();
        }

        $session->set('selected_abonnements_ids', $ids);

        $this->addFlash('success', 'Abonnement sélectionné avec succès!');
        return $this->redirectToRoute('app_both_step_cinq'); // Assurez-vous d'avoir la route correcte ici
    }

    $abonnements = $entityManager->getRepository(Abonnements::class)->createQueryBuilder('a')
        ->where('a.categorie = :kids OR a.categorie = :ados')
        ->setParameters([
            'kids' => 'kids',
            'ados' => 'ados'
        ])
        ->getQuery()
        ->getResult();

    $groupedAbonnements = [];
    foreach ($abonnements as $abonnement) {
        $groupedAbonnements[$abonnement->getDiscipline()][] = $abonnement;
    }

    // Récupération des abonnements pour adultes
$adulteAbonnementsData = $entityManager->getRepository(Abonnements::class)->createQueryBuilder('b')
->where('b.categorie = :adultes')
->setParameter('adultes', 'adultes')
->getQuery()
->getResult();

// Grouper les abonnements par discipline
$adulteAbonnements = [];
foreach ($adulteAbonnementsData as $abonnement) {
$adulteAbonnements[$abonnement->getDiscipline()][] = $abonnement;
}

    return $this->render('registration/registerBothStep4.html.twig', [
        'form' => $form->createView(),
        'kids' => $this->getUser()->getKids(),
        'groupedAbonnements' => $groupedAbonnements,
        'adulteAbonnements' => $adulteAbonnements  // Ajoutez cette ligne
    ]);
}












   
}