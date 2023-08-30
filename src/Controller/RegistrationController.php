<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Kids;
use App\Entity\Users;
use App\Entity\Orders;
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
public function chooseSelfSubscription(Request $request, SessionInterface $session, EntityManagerInterface $entityManager): Response
{
    // Get all selected Abonnements IDs from the request
    $abonnementIds = $request->request->all()['abonnement'] ?? null;

    
    if (empty($abonnementIds)) {
        $this->addFlash('error', 'Veuillez choisir un ou plusieurs abonnements.');
        return $this->redirectToRoute('app_self_subscriptionForm');
    }

    $total = 0;
    foreach ($abonnementIds as $id) {
        $abo = $entityManager->getRepository(Abonnements::class)->find($id);
        $total += $abo->getPrix();
    }

    $order = new Orders();
    $order->setUsers($this->getUser());
    $order->setReference(uniqid());
    $order->setIsPayer(false);
    $order->setIsProcessed(false);
    $order->setPaymentMethod('stripe');
    $order->setTotal($total);
    $order->setCreatedAt(new \DateTimeImmutable());
    $entityManager->persist($order);
    $entityManager->flush();

    // Save the selected Abonnements IDs in the session
    $session->set('selected_abonnement_ids', $abonnementIds);

    return $this->redirectToRoute('app_step_quatre', ['order_id' => $order->getId()]);
}


#[Route('/register/etapes-quatre', name: 'app_step_quatre', methods: ["GET"])]
public function checkoutStripe(Request $request, SessionInterface $session, AbonnementsRepository $abonnementsRepository): Response
{
    $abonnementIds = $session->get('selected_abonnement_ids');

    // Get all selected Abonnements details
    $abonnements = $abonnementsRepository->findBy(['id' => $abonnementIds]);

    $total = array_sum(array_map(function($abo) {
        return $abo->getPrix();
    }, $abonnements));

    $orderId = $request->get('order_id');

    return $this->render('stripe/checkout_stripe.html.twig', [
        'abonnements' => $abonnements,
        'total' => $total,
        'order_id' => $orderId,
        'stripe_key' => $_ENV["STRIPE_KEY"],
        'user' => $this->getUser()
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



    


    #[Route('/register/kids/step4', name: 'register_kids_step_quatre')]
public function showKidsForm(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, AbonnementsRepository $abonnementsRepository): Response {
    
    // Récupération des enfants associés à l'utilisateur
    $kidsData = [];
    foreach ($this->getUser()->getKids() as $kid) {
        $kidsData[] = ['abonnement' => null];
    }

    // Création du formulaire avec les données
    $form = $this->createForm(KidsAdhesionCollectionFormType::class, ['kidsAbonnement' => $kidsData]);
    
    if ($request->isMethod('POST')) {
        // Récupération des enfants de l'utilisateur
        $kids = $this->getUser()->getKids();

        $kidAbonnementAssociations = []; // Initialisation du tableau pour éviter des erreurs potentielles
    
        foreach ($kids as $kidIndex => $kid) {
            // Obtenez l'ID de l'abonnement de la demande pour cet enfant
            $abonnementId = $request->request->get('abonnement_for_kid_' . $kidIndex);

            if ($abonnementId) {
                // vérifiez si l'ID de l'abonnement n'est pas null
                $abonnement = $abonnementsRepository->find($abonnementId);
                if ($abonnement) {
                    $order = new Orders();
                    $order->setUsers($this->getUser());
                    $order->setKid($kid);
                    $order->setReference(uniqid());
                    $order->setIsPayer(false);
                    $order->setIsProcessed(false);
                    $order->setPaymentMethod('stripe');
                    $order->setCreatedAt(new \DateTimeImmutable());
                    $order->setTotal($abonnement->getPrix());
                    
                    $kidAbonnementAssociations[$kid->getId()] = $abonnement->getId();

                    $entityManager->persist($order);
                }
            }
        }

        $entityManager->flush(); // Persistez toutes les commandes en une seule transaction
    
        // Trouver le premier orderId non vide
        $orderId = null;
        foreach ($kidAbonnementAssociations as $kidId => $abonnementId) {
            $orderId = $this->generateUrl('app_kids_step_cinq', ['order_id' => $order->getId()]);
            break;
        }
    
        return $this->redirect($orderId);
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

    return $this->render('registration/registerKidsStep4.html.twig', [
        'form' => $form->createView(),
        'kids' => $this->getUser()->getKids(),
        'groupedAbonnements' => $groupedAbonnements,
    ]);
}

    

    

    

    #[Route('/register/etapes-cinq', name: 'app_kids_step_cinq', methods: ["GET"])]
    public function checkoutStripeKids(
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        AbonnementsRepository $abonnementsRepository,
        Request $request,
        KidsRepository $kidsRepository
    ): Response {
        $orderId = $request->get('order_id');
        
        
        // Récupérer la liste des IDs d'abonnements sélectionnés et les IDs d'enfants associées
        $kidAbonnementAssociations = $session->get('kid_abonnement_associations', []);
    
        $selectedAbonnements = [];
        foreach ($kidAbonnementAssociations as $kidId => $abonnementId) {
            $abonnement = $abonnementsRepository->find($abonnementId);
            if ($abonnement) {
                $selectedAbonnements[$kidId] = $abonnement;
            }
        }
    
        // Création d'un tableau d'enfants avec les IDs comme clés
        $kidsArray = [];
        foreach ($kidsRepository->findBy(['user' => $this->getUser()]) as $kid) {
            $kidsArray[$kid->getId()] = $kid;
        }
    
        // Récupérer toutes les commandes associées à cet utilisateur et qui n'ont pas été traitées
        $orders = $entityManager->getRepository(Orders::class)->findBy(['Users' => $this->getUser(), 'isPayer' => false, 'isProcessed' => false]);
    
        // Calculez le montant total à payer
        $totalPrice = 0;
        foreach ($orders as $order) {
            $totalPrice += $order->getTotal();
        }
    
        // Transmettre les données à la vue
        return $this->render('stripe/kidsCheckout_stripe.html.twig', [
            'selectedAbonnements' => $selectedAbonnements,
            'total' => $totalPrice,
            'stripe_key' => $_ENV["STRIPE_KEY"],
            'order_id' => $orderId,
            'kids' => $kidsArray
        ]);
    }
    






    #[Route('/inscription/etape-trois-bis/', name: 'app_both_subscriptionForm', methods: ["GET", "POST"])]

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



// #[Route('/inscription/etape-quatre-bis/', name: 'app_register_both_step_quatre')]
// public function showBothForm(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, AbonnementsRepository $abonnementsRepository): Response
// {
//     $orderId = $session->get('order_id');

//     // Récupération des enfants associés à l'utilisateur
//     $kidsData = [];
//     foreach ($this->getUser()->getKids() as $kid) {
//         $kidsData[] = ['abonnement' => null];
//     }

//     $form = $this->createForm(KidsAdhesionCollectionFormType::class, ['kidsAbonnement' => $kidsData]);
//     $form->handleRequest($request);

//     if ($form->isSubmitted() && $form->isValid()) {
//         $formData = $form->getData();

//         // 1. Get the price of the user's selected abonnement.
//         $selectedUserAbonnement = $formData['userAbonnement']['abonnement'];
//         $session->set('selected_user_abonnement_id', $selectedUserAbonnement->getId());
//         $userAbonnementPrice = (float) $selectedUserAbonnement->getPrix();

//         // 2. Loop through each selected abonnement for the kids and add their prices.
//         $kidsAbonnementPrice = 0; 
//         $selectedAbonnements = $formData['kidsAbonnement'];
//         $ids = [];
//         foreach ($selectedAbonnements as $kidAbonnement) {
//             $ids[] = $kidAbonnement['abonnement']->getId();
//             $kidsAbonnementPrice += (float) $kidAbonnement['abonnement']->getPrix();
//         }

//         // Compute the total price
//         $totalPrice = $userAbonnementPrice + $kidsAbonnementPrice;

//         $session->set('selected_abonnements_ids', $ids);
        
//         $order = new Orders();
//         $order->setUsers($this->getUser());
//         $order->setReference(uniqid());
//         $order->setIsPayer(false);
//         $order->setIsProcessed(false);
//         $order->setPaymentMethod('stripe');
//         $order->setTotal($totalPrice);
//         $order->setCreatedAt(new \DateTimeImmutable());
//         $entityManager->persist($order);
//         $entityManager->flush();

//         $this->addFlash('success', 'Abonnement sélectionné avec succès!');
//         return $this->redirectToRoute('app_both_step_cinq', ['order_id' => $order->getId()]); 
//     }

//     $abonnements = $entityManager->getRepository(Abonnements::class)->createQueryBuilder('a')
//         ->where('a.categorie = :kids OR a.categorie = :ados')
//         ->setParameters([
//             'kids' => 'kids',
//             'ados' => 'ados'
//         ])
//         ->getQuery()
//         ->getResult();

//     $groupedAbonnements = [];
//     foreach ($abonnements as $abonnement) {
//         $groupedAbonnements[$abonnement->getDiscipline()][] = $abonnement;
//     }

//     // Récupération des abonnements pour adultes
//     $adulteAbonnementsData = $entityManager->getRepository(Abonnements::class)->createQueryBuilder('b')
//         ->where('b.categorie = :adultes')
//         ->setParameter('adultes', 'adultes')
//         ->getQuery()
//         ->getResult();

//     $adulteAbonnements = [];
//     foreach ($adulteAbonnementsData as $abonnement) {
//         $adulteAbonnements[$abonnement->getDiscipline()][] = $abonnement;
//     }

//     return $this->render('registration/registerBothStep4.html.twig', [
//         'form' => $form->createView(),
//         'kids' => $this->getUser()->getKids(),
//         'groupedAbonnements' => $groupedAbonnements,
//         'adulteAbonnements' => $adulteAbonnements,
//         'order_id' => $orderId
//     ]);
// }




#[Route('/inscription/etape-quatre-bis/', name: 'app_register_both_step_quatre')]
public function showBothForm(
    Request $request, 
    EntityManagerInterface $entityManager, 
    SessionInterface $session, 
    AbonnementsRepository $abonnementsRepository
): Response {
   

    // Handle adult subscriptions
    
    
 




    if (!empty($abonnementIds)) {
        $total = 0;
        foreach ($abonnementIds as $id) {
            $abo = $entityManager->getRepository(Abonnements::class)->find($id);
            if ($abo) {  // Ensure the abonnement exists
                $total += $abo->getPrix();
            }
        }

        $order = new Orders();
        $order->setUsers($this->getUser())
              ->setReference(uniqid())
              ->setIsPayer(false)
              ->setIsProcessed(false)
              ->setPaymentMethod('stripe')
              ->setTotal($total)
              ->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($order);
        $entityManager->flush();
        

        // Save the selected Abonnements IDs in the session
        $session->set('selected_abonnement_ids', $abonnementIds);
        return $this->redirectToRoute('app_both_step_cinq', ['orderId' => $order->getId()]);
    }

    // Handle kids' subscriptions
    $kidsData = [];
    foreach ($this->getUser()->getKids() as $kid) {
        $kidsData[] = ['abonnement' => null];
    }

    $form = $this->createForm(KidsAdhesionCollectionFormType::class, ['kidsAbonnement' => $kidsData]);

    if ($request->isMethod('POST')) {
        $kids = $this->getUser()->getKids();

        foreach ($kids as $kidIndex => $kid) {
            $abonnementId = $request->request->get('abonnement_for_kid_' . $kidIndex);

            if ($abonnementId) {
                $abonnement = $abonnementsRepository->find($abonnementId);
                if ($abonnement) {
                    $order = new Orders();
                    $order->setUsers($this->getUser())
                          ->setKid($kid)
                          ->setReference(uniqid())
                          ->setIsPayer(false)
                          ->setIsProcessed(false)
                          ->setPaymentMethod('stripe')
                          ->setCreatedAt(new \DateTimeImmutable())
                          ->setTotal($abonnement->getPrix());

                    $entityManager->persist($order);
                }
            }
        }

        $entityManager->flush();
       
        return $this->redirectToRoute('app_both_step_cinq', ['orderId' => $order->getId()]);
    }

    // Fetch adult and kids subscriptions for the form display
    $adultAbonnements = $entityManager->getRepository(Abonnements::class)
        ->findBy(['categorie' => 'Adultes']);

    $groupedAdultAbonnements = [];
    foreach ($adultAbonnements as $abonnement) {
        $groupedAdultAbonnements[$abonnement->getDiscipline()][] = $abonnement;
    }

    $abonnements = $entityManager->getRepository(Abonnements::class)
        ->createQueryBuilder('a')
        ->where('a.categorie IN (:categories)')
        ->setParameter('categories', ['kids', 'ados'])
        ->getQuery()
        ->getResult();

    $groupedAbonnements = [];
    foreach ($abonnements as $abonnement) {
        $groupedAbonnements[$abonnement->getDiscipline()][] = $abonnement;
    }

    return $this->render('registration/registerBothStep4.html.twig', [
        'form' => $form->createView(),
        'kids' => $this->getUser()->getKids(),
        'groupedAbonnements' => $groupedAbonnements,
        'groupedAdultAbonnements' => $groupedAdultAbonnements
    ]);
}






#[Route('/register/etape5/{orderId}', name: 'app_both_step_cinq', methods: ["GET"])]
public function checkoutStripeBoth(SessionInterface $session, AbonnementsRepository $abonnementsRepository, Request $request): Response
{
    $abonnementIds = $request->request->all()['adult_abonnement'] ?? [];
   

    $userName = $this->getUser()->getNom();

    $orderId = $request->get('order_id');
    
    // Récupérer l'ID de l'abonnement sélectionné pour l'utilisateur (à partir de la session)
    $userAbonnementId = $session->get('selected_user_abonnement_id');
    // Récupérer l'objet Abonnement pour l'utilisateur
    $userAbonnement = $abonnementsRepository->find($userAbonnementId);
    $totalPrice = $userAbonnement->getPrix();

    // Récupérer la liste des IDs d'abonnements sélectionnés pour les enfants (à partir de la session)
    $kidsAboIds = $session->get('selected_abonnements_ids');

    // Récupérer les objets Abonnements pour les enfants
    $kidsAbonnements = [];
    foreach ($kidsAboIds as $kidIndex => $abonnementId) {
        $abonnement = $abonnementsRepository->find($abonnementId);
        if ($abonnement) {
            $kidsAbonnements[$kidIndex] = $abonnement;
            $totalPrice += $abonnement->getPrix();
        }
    }

    $kidsData = $this->getUser()->getKids(); 
    $kidsNames = [];
    foreach ($kidsData as $kid) {
    $kidsNames[] = $kid->getNom(); // Assuming getName() is the function to get the kid's name
}


    return $this->render('stripe/bothCheckout_stripe.html.twig', [
        'userName' => $userName,
        'kidsNames' => $kidsNames,
        'userAbonnement' => $userAbonnement,
        'kidsAbonnements' => $kidsAbonnements,
        'total' => $totalPrice,
        'stripe_key' => $_ENV["STRIPE_KEY"],
        'order_id' => $orderId
    ]);
}






   
}