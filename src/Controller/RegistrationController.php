<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Kids;
use App\Entity\Users;
use App\Entity\Orders;
use App\Entity\Adhesions;
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

use App\Repository\AdhesionsRepository;
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

#[Route('/register/etapes-trois', name: 'app_self_subscriptionForm_submit', methods: ["POST"])]  // Choix de l  'abonnement
public function chooseSelfSubscription(Request $request, SessionInterface $session, EntityManagerInterface $entityManager): Response
{
    // Get all selected Abonnements IDs from the request
    $abonnementIds = $request->request->all()['abonnement'] ?? null;

    if (empty($abonnementIds)) {
        $this->addFlash('error', 'Veuillez choisir un ou plusieurs abonnements.');
        return $this->redirectToRoute('app_self_subscriptionForm');
    }

    // Identifiants des abonnements K-PROG
    $kProgDiscountIds = [1, 2, 3];
    $hasAnyKProg = count(array_intersect($abonnementIds, $kProgDiscountIds)) > 0;
    $hasOtherSubscriptions = count($abonnementIds) > 1; // To ensure more than one subscription is chosen
    
    // Calculate total amount
    $total = 0;
    foreach ($abonnementIds as $id) {
        $abo = $entityManager->getRepository(Abonnements::class)->find($id);
        if($abo) { // Always good to check
            $total += $abo->getPrix();
        }
    }

    // Apply 20% discount if conditions are met
    if ($hasAnyKProg && $hasOtherSubscriptions) {
        $total *= 0.8;
        
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
    $session->set('order_id', $order->getId());

    return $this->redirectToRoute('app_step_quatre', [
        'order_id' => $order->getId(),
        'total' => $total
    ]);
}





#[Route("/register/vider-panier", name:"empty_cart")]
public function emptyCart(SessionInterface $session, EntityManagerInterface $entityManager): Response {
    $orderId = $session->get('order_id');
    if ($orderId) {
        $order = $entityManager->getRepository(Orders::class)->find($orderId);
        if ($order) {
            $entityManager->remove($order);
            $entityManager->flush();
        }
    }
    
    $session->remove('selected_abonnement_ids');
    $session->remove('order_id');
    
    return $this->redirectToRoute('app_step_quatre');
}


#[Route("/register/vider-panier-kids", name:"empty_cart_kids")]
public function emptyCartKids(SessionInterface $session, EntityManagerInterface $entityManager): Response {
    $user = $this->getUser();
    
    // Récupérer toutes les commandes associées à cet utilisateur et qui n'ont pas été traitées
    $existingOrders = $entityManager->getRepository(Orders::class)->findBy(['Users' => $user, 'isPayer' => false, 'isProcessed' => false]);
    
    // Supprimer toutes ces commandes
    foreach ($existingOrders as $order) {
        $entityManager->remove($order);
    }
    
    $entityManager->flush();
    
    $session->remove('selected_abonnement_ids');
    $session->remove('order_id');
    
    return $this->redirectToRoute('register_kids_step_quatre');
}








#[Route('/register/etapes-quatre', name: 'app_step_quatre', methods: ["GET"])] //Choix du moyen de paiement + redirection stripe
public function checkoutStripe(Request $request, SessionInterface $session, AbonnementsRepository $abonnementsRepository, EntityManagerInterface $entityManager): Response
{
    $abonnementIds = $session->get('selected_abonnement_ids');
    $abonnements = $abonnementsRepository->findBy(['id' => $abonnementIds]);

    $orderId = $request->get('order_id');

    if (!$orderId) {
        // Pas d'ID de commande fourni
        $this->addFlash('error', 'Aucune commande valide. Veuillez sélectionner un abonnement.');
        return $this->redirectToRoute('app_self_subscriptionForm'); // Redirigez vers la page de sélection de l'abonnement ou une autre page pertinente.
    }

    // Récupération du total depuis l'entité `Orders`
    $order = $entityManager->getRepository(Orders::class)->find($orderId);
    if (!$order) {
        throw $this->createNotFoundException('Order not found');
    }
    $total = $order->getTotal();

    return $this->render('stripe/checkout_stripe.html.twig', [
        'abonnements' => $abonnements,
        'total' => $total,
        'order_id' => $orderId,
        'stripe_key' => $_ENV["STRIPE_KEY"],
        'user' => $this->getUser()
    ]);
}




#[Route('/inscription/etape-trois-kids', name: 'app_kids_subscriptionForm', methods: ["GET", "POST"])]

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







 #[Route('/register/kids/step4', name:'register_kids_step_quatre')]
 
public function showKidsForm(
    Request $request, 
    EntityManagerInterface $entityManager, 
    SessionInterface $session, 
    AbonnementsRepository $abonnementsRepository,
    AdhesionsRepository $adhesionsRepository
): Response {
    $kids = $this->getUser()->getKids();
    $kidsData = [];
    foreach ($kids as $kid) {
        $kidsData[] = ['abonnement' => null];
    }

    $form = $this->createForm(KidsAdhesionCollectionFormType::class, ['kidsAbonnement' => $kidsData]);
    $kidAbonnementAssociations = [];
    $lastOrder = null;
    $activeAdhesion = $adhesionsRepository->findActiveAdhesionsForUser($this->getUser());
    $hasActiveAdhesion = count($activeAdhesion) > 0;

    $existingOrders = $entityManager->getRepository(Orders::class)->findBy(['Users' => $this->getUser(), 'isPayer' => false, 'isProcessed' => false]);

    // Supprimer les commandes existantes
    foreach ($existingOrders as $order) {
        $entityManager->remove($order);
    }

    if ($request->isMethod('POST')) {
        foreach ($kids as $kidIndex => $kid) {
            $abonnementId = $request->request->get('abonnement_for_kid_' . $kidIndex);
            
            if (!$abonnementId) {
                continue;
            }

            $abonnement = $abonnementsRepository->find($abonnementId);
            if (!$abonnement) {
                continue;
            }
            
            $kidAbonnementAssociations[$kid->getId()] = $abonnement->getId();

            $order = new Orders();
            $order->setUsers($this->getUser());
            $order->setKid($kid);
            $order->setReference(uniqid());
            $order->setIsPayer(false);
            $order->setIsProcessed(false);
            $order->setPaymentMethod('stripe');

            $orderTotal = $abonnement->getPrix();
            if ($hasActiveAdhesion) {
                $orderTotal *= 0.8; // Apply 20% discount
            }
            $order->setTotal($orderTotal);

            $order->setCreatedAt(new \DateTimeImmutable());
            $lastOrder = $order;
            $entityManager->persist($order);
        }

        $session->set('kid_abonnement_associations', $kidAbonnementAssociations);
        $entityManager->flush();

        if ($lastOrder) {
            $session->set('order_id', $lastOrder->getId());
            return $this->redirectToRoute('app_kids_step_cinq', ['order_id' => $lastOrder->getId()]);
        } else {
            $this->addFlash('warning', 'Veuillez sélectionner au moins un abonnement pour un de vos enfants.');
        }
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
        'kids' => $kids,
        'groupedAbonnements' => $groupedAbonnements,
    ]);
}




    
    

    

#[Route('/register/etapes-cinq', name: 'app_kids_step_cinq', methods: ["GET"])]
public function checkoutStripeKids(
    EntityManagerInterface $entityManager,
    SessionInterface $session,
    AbonnementsRepository $abonnementsRepository,
    Request $request,
    KidsRepository $kidsRepository,
    AdhesionsRepository $adhesionsRepository
): Response {
    $user = $this->getUser();
    
    // Vérifiez si l'utilisateur a un abonnement actif
    $activeAdhesion = $adhesionsRepository->findActiveAdhesionsForUser($user); // la function est dans adhesionsRepository

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
    foreach ($kidsRepository->findBy(['user' => $user]) as $kid) {
        $kidsArray[$kid->getId()] = $kid;
    }
    
    // Récupérer toutes les commandes associées à cet utilisateur et qui n'ont pas été traitées
    $orders = $entityManager->getRepository(Orders::class)->findBy(['Users' => $user, 'isPayer' => false, 'isProcessed' => false]);
    
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
        'order_id' => $request->get('order_id'),
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






#[Route('/inscription/etape-quatre-bis/', name: 'app_register_both_step_quatre')]
public function showBothForm(
    Request $request, 
    EntityManagerInterface $entityManager, 
    SessionInterface $session, 
    AbonnementsRepository $abonnementsRepository
): Response {
    
    $kidsData = [];
    foreach ($this->getUser()->getKids() as $kid) {
        $kidsData[] = ['abonnement' => null];
    }

    $form = $this->createForm(KidsAdhesionCollectionFormType::class, ['kidsAbonnement' => $kidsData]);

    $totalPriceAdult = 0;
    $totalPriceKids = 0;

    if ($request->isMethod('POST')) {
        $selectedAbonnements = $request->request->all('adult_abonnement');
        $orderIds = [];
        
        if ($selectedAbonnements) {
            foreach ($selectedAbonnements as $abonnementId) {
                $abonnement = $abonnementsRepository->find($abonnementId);
                if ($abonnement) {
                    $totalPriceAdult += $abonnement->getPrix();
                }
            }
        }

        $kids = $this->getUser()->getKids();
        foreach ($kids as $kidIndex => $kid) {
            $abonnementId = $request->request->get('abonnement_for_kid_' . $kidIndex);
            if ($abonnementId) {
                $abonnement = $abonnementsRepository->find($abonnementId);
                if ($abonnement) {
                    $totalPriceKids += $abonnement->getPrix();
                }
            }
        }

        // Calculez le discountFactor après avoir déterminé les totaux pour les adultes et les enfants.
        $applyDiscount = $totalPriceAdult > 0 && $totalPriceKids > 0;
        $discountFactor = $applyDiscount ? 0.8 : 1.0;

        if ($selectedAbonnements) {
            foreach ($selectedAbonnements as $abonnementId) {
                $abonnement = $abonnementsRepository->find($abonnementId);
                if ($abonnement) {
                    $order = new Orders();
                    $order->setUsers($this->getUser())
                          ->setReference(uniqid())
                          ->setIsPayer(false)
                          ->setIsProcessed(false)
                          ->setPaymentMethod('stripe')
                          ->setCreatedAt(new \DateTimeImmutable())
                          ->setTotal($abonnement->getPrix() * $discountFactor)
                          ->addAbonnement($abonnement);
    
                    $entityManager->persist($order);
                    $orderIds[] = $order;
                }
            }
            $entityManager->flush();
        }

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
                          ->setTotal($abonnement->getPrix() * $discountFactor)
                          ->addAbonnement($abonnement);

                    $entityManager->persist($order);
                    $orderIds[] = $order;
                }
            }
        }
        $entityManager->flush();
        $session->set('order_ids', $orderIds);
 
        return $this->redirectToRoute('app_both_step_cinq');
    }

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
        'groupedAdultAbonnements' => $groupedAdultAbonnements,
    ]);
}








#[Route('/register/etape5', name: 'app_both_step_cinq', methods: ["GET"])]
public function checkoutStripeBoth(SessionInterface $session, AbonnementsRepository $abonnementsRepository, Request $request, EntityManagerInterface $em): Response
{
    $orderIds = $session->get('order_ids', []);

    $user = $this->getUser();
    $userName = $user->getNom();
    $nullKidOrders = $em->getRepository(Orders::class)->findNullKidOrders($user); 
    
    $totalPriceAdult = 0;
    $userAbonnement = [];
    
    foreach ($nullKidOrders as $order) {
        $totalPriceAdult += $order->getTotal();
        $userAbonnement[] = $order;
    }
   

   

    
    $kidsData = $user->getKids(); 
    $kidsNames = [];

    foreach ($kidsData as $kid) {
        $kidsNames[] = $kid->getNom();
    }

    $kidsAbonnementsWithPrices = [];
    $totalPriceKids = 0; 
    
    foreach ($kidsData as $kid) {
        $kidAbonnements = [];
        $totalPriceKid = 0;
  
        
        foreach ($kid->getOrders() as $kidOrder) {
            foreach ($kidOrder->getAbonnement() as $abonnement) {
                $kidAbonnements[] = [
                    'abonnement' => $abonnement,
                    'prix' => $abonnement->getPrix(),
                ];
                $totalPriceKid += $abonnement->getPrix(); 
                

            }
        }
        

        
        $kidsAbonnementsWithPrices[$kid->getNom()] = $kidAbonnements;
        $totalPriceKids += $totalPriceKid; 
         
    }
    
    $totalPrice = $totalPriceAdult + $totalPriceKids; 

    // Apply 20% discount if the user has taken an abonnement for himself and at least one for his kids
    // if ($totalPriceAdult > 0 && $totalPriceKids > 0) {
    //     $totalPrice *= 0.8;
    // }

    $session->set('checkout_data', [
        'kidsAbonnementsWithPrices' => $kidsAbonnementsWithPrices,
        'totalAdult' => $totalPriceAdult,
        'totalKids' => $totalPriceKids,
        'total' => $totalPrice,
        'userAbonnement' => $userAbonnement,
        'order_id' => $orderIds,
    ]);
  
    return $this->render('stripe/bothCheckout_stripe.html.twig', [
        'userName' => $userName,
        'stripe_key' => $_ENV["STRIPE_KEY"],
        'order_id' => $orderIds,
        'total' => $totalPrice,
        'userAbonnement' => $userAbonnement,
        'kidsData' => $kidsData,
        'kidsNames' => $kidsNames,
        'kidsAbonnementsWithPrices' => $kidsAbonnementsWithPrices,
    ]);
}







   
}