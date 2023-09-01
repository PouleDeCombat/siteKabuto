<?php
 
namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Kids;
use App\Entity\Orders;
use App\Entity\Adhesions;
use App\Entity\Abonnements;
use App\Service\CartService;
use Stripe\Checkout\Session;
use App\Entity\OrdersDetails;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\KidsRepository;
use App\Repository\OrdersRepository;
use App\Repository\ProductsRepository;
use App\Repository\AdhesionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AbonnementsRepository;
use App\Repository\OrdersDetailsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }
    #[Route('/stripe', name: 'app_stripe')]
public function index(SessionInterface $session, ProductsRepository $productsRepository, OrdersDetailsRepository $ordersDetailsRepository): Response
{
    $panier = $session->get('panier', []);
    $data = [];
    $total = 0;

    foreach ($panier as $key => $quantity) {
        $parts = explode('-', $key);
        
        // Vérifiez si $parts a au moins deux éléments
        if (count($parts) < 2) {
            continue; // Ignore et passe à l'itération suivante
        }

        list($id, $size) = $parts;
        $product = $productsRepository->find($id);
        
        if (!$product) {
            continue; // Si le produit n'est pas trouvé, ignorez cette itération
        }
        
        $orderDetail = $ordersDetailsRepository->findOneBy(['products' => $product, 'size' => $size]);

        if (!$orderDetail) {
            continue; // Si la taille spécifique du produit n'est pas trouvée, ignorez cette itération
        }
        $realSize = $sizes[$size] ?? $size;
        $data[] = [
            'product' => $product,
            'quantity' => $quantity,
            'size' => $realSize
        ];
        


        $total += $product->getPrice() * $quantity;
    }

    $sizes = [
        '1' => 'S',
        '2' => 'M',
        '5' => 'L',
        '6' => 'XL',
        '7' => '12 Oz',
        '8' => '14 Oz',
        '9' => 'S/M',
        '10' => 'L/XL',
        '11' => 'Taille Unique',

        
    ];

    return $this->render('stripe/index.html.twig', [
        'stripe_key' => $_ENV["STRIPE_KEY"],
        'data' => $data,
        'total' => $total,
        'size' => $sizes,
    ]);
}



#[Route('/stripe/create-charge', name: 'app_stripe_charge', methods: ['POST'])]
public function createCharge(Request $request, SessionInterface $session, AbonnementsRepository $abonnementsRepository)
{
    // Récupérer la liste des IDs d'abonnements sélectionnés
    $selectedAbonnementsIds = $session->get('selected_abonnements_ids', []);
    $selectedAbonnements = $abonnementsRepository->findBy(['id' => $selectedAbonnementsIds]);

    // Calculer le total
    $total = 0;
    foreach ($selectedAbonnements as $abonnement) {
        $total += $abonnement->getPrix();
    }

    // Convertir le total en centimes pour Stripe
    $totalCents = $total * 100;

    // Traitement Stripe
    Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET"]);
    Stripe\Charge::create([
        "amount" => $totalCents,
        "currency" => "eur",
        "source" => $request->request->get('stripeToken'),
        "description" => "Achat d'abonnement pour les enfants"
    ]);

    $this->addFlash('success', 'Payment Successful!');
    return $this->redirectToRoute('app_stripe', [], Response::HTTP_SEE_OTHER);
}


 
 
#[Route('/stripe/stripe-session/{orderId}', name: 'payment_stripe')]
public function stripeCheckOut(
    EntityManagerInterface $entityManager, 
    $orderId, 
    UrlGeneratorInterface $generator, 
    SessionInterface $session
): RedirectResponse {
    $orderRepository = $entityManager->getRepository(Orders::class);
    $order = $orderRepository->findOneBy(['id' => $orderId]);

    if (!$order) {
        return $this->redirectToRoute('app_landing');
    }

    \Stripe\Stripe::setApiKey('sk_test_51NZyE0ECy2gfABuUGrCGxxTBYM2h9hzbF4Qwt9vpWEPBkNB2KrYyfg5txidH37JqfrORvoR2Y6iA33JRaE1bPR9i00V1QED7kU');

    // Get abonnements of the order
    $abonnementIds = $session->get('selected_abonnement_ids');
    $abonnements = $entityManager->getRepository(Abonnements::class)->findBy(['id' => $abonnementIds]);

    // Transform abonnements to Stripe line items
    $lineItems = [];
    foreach ($abonnements as $abo) {
        $userName = $order->getUsers()->getNom() . " " . $order->getUsers()->getPrenom();
        $description = "Durée: " . $abo->getDurée() . ", Bénéficiaire: " . $userName;
        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $abo->getDiscipline(),
                    'description' => $description
                ],
                'unit_amount' => $abo->getPrix() * 100,
            ],
            'quantity' => 1,
        ];
    }

    $identifiantsKPROG = [1, 2, 3];
    $hasAnyKProg = count(array_intersect($abonnementIds, $identifiantsKPROG)) > 0;
    $hasOtherSubscriptions = count($abonnementIds) > 1;

    if ($hasAnyKProg && $hasOtherSubscriptions) {
        // Apply 20% discount directly to line items
        foreach ($lineItems as &$item) {
            $item['price_data']['unit_amount'] *= 0.8; // Apply 20% discount
        }
        unset($item);  // Important when using reference in a foreach loop

        // Add an item for the K-PROG discount message with 0 amount
        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => 'Remise K-PROG',
                    'description' => 'Vous bénéficiez d’une réduction de 20% grâce au K-PROG!'
                ],
                'unit_amount' => 0, // 0 because it's just an informational item
            ],
            'quantity' => 1,
        ];
    }

    $checkout_session = Session::create([
        'payment_method_types' => ['card'],
        'customer_email' => $order->getUsers()->getEmail(),
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => $generator->generate('payment_success', ['orderId' => $orderId], UrlGeneratorInterface::ABSOLUTE_URL),
        'cancel_url' => $generator->generate('payment_error', ['orderId' => $orderId], UrlGeneratorInterface::ABSOLUTE_URL),
    ]);

    return new RedirectResponse($checkout_session->url);
}





#[Route('/stripe/error/{orderId}', name: 'payment_error')]
public function stripeError(EntityManagerInterface $entityManager,$orderId):Response
{

    return $this->render('stripe/error.html.twig');
}



#[Route('/stripe/kids-stripe-session/{orderId}', name: 'payment_stripe_kids')]
public function stripeKidsCheckOut(
    EntityManagerInterface $entityManager, 
    $orderId, 
    UrlGeneratorInterface $generator, 
    SessionInterface $session, 
    AbonnementsRepository $abonnementsRepository, 
    KidsRepository $kidsRepository, 
    AdhesionsRepository $adhesionsRepository
): RedirectResponse {

    $orderRepository = $entityManager->getRepository(Orders::class);
    $order = $orderRepository->findOneBy(['id' => $orderId]);

    if(!$order){
        return $this->redirectToRoute('app_home');
    }

    \Stripe\Stripe::setApiKey('sk_test_51NZyE0ECy2gfABuUGrCGxxTBYM2h9hzbF4Qwt9vpWEPBkNB2KrYyfg5txidH37JqfrORvoR2Y6iA33JRaE1bPR9i00V1QED7kU');
    
    // Récupérez les associations entre les enfants et leurs abonnements
    $kidAbonnementAssociations = $session->get('kid_abonnement_associations', []);
    
    // Vérifiez si l'utilisateur a un abonnement actif
    $activeAdhesions = $adhesionsRepository->findActiveAdhesionsForUser($order->getUsers());
    $hasActiveAdhesion = count($activeAdhesions) > 0;

    $lineItems = [];
    foreach ($kidAbonnementAssociations as $kidId => $abonnementId) {
        $abonnement = $abonnementsRepository->find($abonnementId);
        $kid = $kidsRepository->find($kidId);
        
        if ($abonnement && $kid) {
            $name = $kid->getPrenom() . " " . $kid->getNom() . " - " . $abonnement->getDiscipline();
            $description = "Durée: " . $abonnement->getDurée();
            $unitAmount = $abonnement->getPrix() * 100;

            // Si l'utilisateur a un abonnement actif, appliquez une réduction de 20%
            if ($hasActiveAdhesion) {
                $unitAmount *= 0.8;
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $name,
                        'description' => $description,
                    ],
                    'unit_amount' => $unitAmount,
                ],
                'quantity' => 1, 
            ];
        }
    }

    // Si l'utilisateur a un abonnement actif, ajoutez un élément pour indiquer la réduction
    if ($hasActiveAdhesion) {
        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => 'Remise Familiale',
                    'description' => 'Vous bénéficiez d’une réduction de 20% pour un abonnement familial!'
                ],
                'unit_amount' => 0, // 0 car c'est juste un élément informatif
            ],
            'quantity' => 1,
        ];
    }

    $checkout_session = Session::create([
        'payment_method_types' => ['card'],
        'customer_email' => $order->getUsers()->getEmail(),
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => $generator->generate('payment_kids_success', ['orderId' => $orderId], UrlGeneratorInterface::ABSOLUTE_URL),
        'cancel_url' => $generator->generate('payment_kids_error', ['orderId' => $orderId], UrlGeneratorInterface::ABSOLUTE_URL),
    ]);

    return new RedirectResponse($checkout_session->url);
}



// #[Route('/stripe/kids-success', name: 'payment_kids_success')]
// public function stripeKidsSuccess(EntityManagerInterface $entityManager):Response
// {
//     return $this->render('stripe/success.html.twig');
// }

#[Route('/stripe/kids-error', name: 'payment_kids_error')]
public function stripeKidsError(EntityManagerInterface $entityManager):Response
{
    return $this->render('stripe/error.html.twig');
}







#[Route('/stripe/checkout-session', name: 'payment_stripe_checkout')]
public function bothStripeCheckOut(EntityManagerInterface $entityManager, UrlGeneratorInterface $generator, OrdersRepository $orderRepository, SessionInterface $session): RedirectResponse
{
    $checkoutData = $session->get('checkout_data', []);
    $orderObjects = $checkoutData['order_id'] ?? [];

    if (!$orderObjects) {
        return $this->redirectToRoute('app_landing');
    }

    $totalAmount = 0;
    $orderIds = [];

    // Calculer le prix total de toutes les commandes
    foreach ($orderObjects as $order) {
        $orderIds[] = $order->getId();
        $totalAmount += $order->getTotal();
    }

    if ($totalAmount <= 0) {
        throw new \Exception("No valid order found!"); 
    }

    \Stripe\Stripe::setApiKey('sk_test_51NZyE0ECy2gfABuUGrCGxxTBYM2h9hzbF4Qwt9vpWEPBkNB2KrYyfg5txidH37JqfrORvoR2Y6iA33JRaE1bPR9i00V1QED7kU');

    $lineItems = [[
        'price_data' => [
            'currency' => 'eur',
            'product_data' => [
                'name' => "Vos abonnements",
            ],
            'unit_amount' => $totalAmount * 100,  // Total en centimes
        ],
        'quantity' => 1,
    ]];

    $orderIdsString = implode(',', $orderIds);

    $successRoute = 'payment_success_both';
    $errorRoute = 'payment_error';

    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => $generator->generate($successRoute, ['orderIds' => $orderIdsString], UrlGeneratorInterface::ABSOLUTE_URL),
        // 'cancel_url' => $generator->generate($errorRoute, ['orderIds'=> $orderIdsString], UrlGeneratorInterface::ABSOLUTE_URL),
    ]);

    return new RedirectResponse($checkout_session->url);
}






    #[Route('/stripe/success/{orderId}', name: 'payment_success')]
    public function onPaymentSuccess($orderId, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        // 1. Récupérez les détails de la commande via l'ID de la commande.
        $order = $entityManager->getRepository(Orders::class)->find($orderId);
    
        if (!$order) {
            throw $this->createNotFoundException('La commande n\'a pas été trouvée.');
        }
        $order->setIsPayer(true);
        $order->setIsProcessed(true);

        $entityManager->flush();
    
        // 2. Récupérez l'utilisateur via la commande.
        $user = $order->getUsers();
    
        // Récupérez les abonnements sélectionnés de la session.
        $abonnementIds = $session->get('selected_abonnement_ids');
        foreach ($abonnementIds as $abonnementId) {
            $abonnement = $entityManager->getRepository(Abonnements::class)->find($abonnementId);
            if (!$abonnement) {
                continue;
            }
    
            $adhesion = new Adhesions();
        $adhesion->setUser($order->getUsers());
        $adhesion->addAbonnement($abonnement);
        $startDate = new \DateTime(); // Setting start date first
        $adhesion->setDateDebut($startDate);
        $endDate = $this->calculateEndDate($startDate, $abonnement->getDurée());
        $adhesion->setDateFin($endDate);

        $entityManager->persist($adhesion);
        }
    
        // Validez toutes les adhésions que nous venons de créer.
        $entityManager->flush();
    
        // Redirigez l'utilisateur vers une page de succès ou là où vous le souhaitez.
        return $this->render('stripe/success.html.twig');
    }

    private function calculateEndDate(\DateTime $startDate, string $duration): \DateTime
{
    $endDate = clone $startDate;

    switch ($duration) {
        case '1 MOIS':
            $endDate->modify('+1 month');
            break;
        case '3 MOIS':
            $endDate->modify('+3 months');
            break;
        case 'ANNUEL':
            $juneEndStr = $startDate->format('Y') . '-06-30';
            if ($startDate->format('Y-m-d') <= $juneEndStr) {
                $endDate = new \DateTime($juneEndStr);
            } else {
                $endDate = new \DateTime(($startDate->format('Y') + 1) . '-06-30');
            }
            break;
        default:
            throw new \Exception("Duration value not recognized: $duration");
    }
    return $endDate;
}


    



    #[Route('/payment/cash-check/{orderId}', name: 'payment_by_cash_or_check')]
    public function onPaymentByCashOrCheck($orderId, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        // 1. Récupérez les détails de la commande via l'ID de la commande.
        $order = $entityManager->getRepository(Orders::class)->find($orderId);



        if (!$order) {
            throw $this->createNotFoundException('La commande n\'a pas été trouvée.');
        }

        $order->setIsPayer(false);
        $order->setIsProcessed(true);

        $entityManager->flush();

        // 2. Récupérez l'utilisateur via la commande.
        $user = $order->getUsers();

        // Récupérez les abonnements sélectionnés de la session.
        $abonnementIds = $session->get('selected_abonnement_ids');
        foreach ($abonnementIds as $abonnementId) {
            $abonnement = $entityManager->getRepository(Abonnements::class)->find($abonnementId);

            if (!$abonnement) {
                continue;
            }

            $adhesion = new Adhesions();
        $adhesion->setUser($order->getUsers());
        $adhesion->addAbonnement($abonnement);
        $startDate = new \DateTime(); // Setting start date first
        $adhesion->setDateDebut($startDate);
        $endDate = $this->calculateEndDate($startDate, $abonnement->getDurée());
        $adhesion->setDateFin($endDate);

        $entityManager->persist($adhesion);
        }

        $entityManager->flush();

        return $this->render('stripe/successLiquide.html.twig');
    }



    #[Route('/stripe/kids-success/{orderId}', name: 'payment_kids_success')]
public function onKidsPaymentSuccess($orderId, EntityManagerInterface $entityManager, SessionInterface $session, AbonnementsRepository $abonnementsRepository): Response
{
    // Récupérer toutes les commandes pour cet utilisateur qui ne sont pas encore payées ou traitées
    $orders = $entityManager->getRepository(Orders::class)->findBy([
        'Users' => $this->getUser(), 
        'isPayer' => false, 
        'isProcessed' => false
    ]);

    // Récupérer les IDs d'abonnement depuis la session
    $kidAbonnementAssociations = $session->get('kid_abonnement_associations', []);

    // Parcourir toutes les commandes
    foreach ($orders as $order) {
        $order->setIsPayer(true);
        $order->setIsProcessed(true);
    
        // Récupérer l'enfant associé à la commande
        $kid = $order->getKid();
        
        // Assurez-vous que la commande est associée à un enfant
        if (!$kid) {
            continue;
        }
    
        $kidId = $kid->getId();
        
        // Vérifiez si une association d'abonnement existe pour cet enfant
        if (array_key_exists($kidId, $kidAbonnementAssociations)) {
            $abonnementId = $kidAbonnementAssociations[$kidId];
            $abonnement = $abonnementsRepository->find($abonnementId);
    
            // Vérifiez si l'abonnement existe
            if ($abonnement) {
                $adhesion = new Adhesions();
                $adhesion->setUser($this->getUser());
                $adhesion->setKids($kid);
                $adhesion->addAbonnement($abonnement);
    
                // Utiliser la méthode calculateEndDate pour déterminer la date de fin
                $startDate = new \DateTime();
                $endDate = $this->calculateEndDate($startDate, $abonnement->getDurée());
    
                // Définir les dates de début et de fin de l'adhésion
                $adhesion->setDateDebut($startDate);
                $adhesion->setDateFin($endDate);
    
                $entityManager->persist($adhesion);
            }
        }
    }
    
    $entityManager->flush();
    
    return $this->render('stripe/success.html.twig');
}
    
    
    
    
#[Route('/payment/kids-cash-check/{orderId}', name: 'payment_kids_by_cash_or_check')]
public function onKidsPaymentByCashOrCheck($orderId, EntityManagerInterface $entityManager, SessionInterface $session, AbonnementsRepository $abonnementsRepository): Response
{
    // Récupérez toutes les commandes pour cet utilisateur qui ne sont pas encore payées ou traitées
    $orders = $entityManager->getRepository(Orders::class)->findBy([
        'Users' => $this->getUser(),
        'isPayer' => false,
        'isProcessed' => false
    ]);

    // Récupérer les IDs d'abonnement depuis la session
    $kidAbonnementAssociations = $session->get('kid_abonnement_associations', []);

    // Parcourir toutes les commandes
    foreach ($orders as $order) {
        $order->setIsPayer(false);
        $order->setIsProcessed(true);

        // Récupérer l'enfant associé à la commande
        $kid = $order->getKid();

        if (!$kid) {
            continue;  // Si la commande n'est pas associée à un enfant, passez à la suivante.
        }

        $kidId = $kid->getId();

        if (array_key_exists($kidId, $kidAbonnementAssociations)) {
            $abonnementId = $kidAbonnementAssociations[$kidId];
            $abonnement = $abonnementsRepository->find($abonnementId);

            if ($abonnement) {
                $adhesion = new Adhesions();
                $adhesion->setUser($this->getUser());
                $adhesion->setKids($kid);
                $adhesion->addAbonnement($abonnement);

                $startDate = new \DateTime();
                $endDate = $this->calculateEndDate($startDate, $abonnement->getDurée());

                $adhesion->setDateDebut($startDate);
                $adhesion->setDateFin($endDate);

                $entityManager->persist($adhesion);
            }
        }
    }

    $entityManager->flush();

    return $this->render('stripe/success.html.twig');  // Renvoie vers un template pour le succès du paiement en espèces.
}




    

    
#[Route('/stripe/both-success', name: 'payment_success_both')]
public function onBothPaymentSuccess(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, AbonnementsRepository $abonnementsRepository): Response
{
    $orderIdsString = $request->query->get('orderIds');
    $orderIds = explode(',', $orderIdsString);

    // Parcourez tous les orderIds pour traiter chacune des commandes. 
    foreach ($orderIds as $orderId) {
        $order = $entityManager->getRepository(Orders::class)->find($orderId);

        if (!$order) {
            continue;  // Continuez avec le prochain orderId si celui-ci n'est pas trouvé.
        }

        $order->setIsPayer(true);
        $order->setIsProcessed(true);
        $entityManager->flush();

        $user = $order->getUsers();

        // Pour les abonnements de l'utilisateur
        $abonnementIds = $session->get('selected_abonnement_ids');
        foreach ($abonnementIds as $abonnementId) {
            $abonnement = $entityManager->getRepository(Abonnements::class)->find($abonnementId);
            if (!$abonnement) {
                continue;
            }

            $adhesion = new Adhesions();
            $adhesion->setUser($order->getUsers());
            $adhesion->addAbonnement($abonnement);
            $startDate = new \DateTime();
            $endDate = $this->calculateEndDate($startDate, $abonnement->getDurée());
            $adhesion->setDateDebut($startDate);
            $adhesion->setDateFin($endDate);

            $entityManager->persist($adhesion);
        }

        // Pour les abonnements des enfants
        $kid = $order->getKid();

        if ($kid) {
            $kidId = $kid->getId();
            $kidAbonnementAssociations = $session->get('kid_abonnement_associations', []);

            if (array_key_exists($kidId, $kidAbonnementAssociations)) {
                $abonnementId = $kidAbonnementAssociations[$kidId];
                $abonnement = $abonnementsRepository->find($abonnementId);

                if ($abonnement) {
                    $adhesion = new Adhesions();
                    $adhesion->setUser($user);
                    $adhesion->setKids($kid);
                    $adhesion->addAbonnement($abonnement);
                    $startDate = new \DateTime();
                    $endDate = $this->calculateEndDate($startDate, $abonnement->getDurée());
                    $adhesion->setDateDebut($startDate);
                    $adhesion->setDateFin($endDate);

                    $entityManager->persist($adhesion);
                }
            }
        }

        $entityManager->flush();
    }

    return $this->render('stripe/success.html.twig');
}




//   #[Route('/payment/both-cash-check/{orderId}', name: 'payment_both_by_cash_or_check')]
 
// public function onBothPaymentByCashOrCheck($orderId, EntityManagerInterface $entityManager, SessionInterface $session, AbonnementsRepository $abonnementsRepository): Response
// {
//     // Récupérez toutes les commandes pour cet utilisateur qui ne sont pas encore payées ou traitées
//     $orders = $entityManager->getRepository(Orders::class)->findBy([
//         'Users' => $this->getUser(),
//         'isPayer' => false,
//         'isProcessed' => false
//     ]);

//     // Pour les abonnements de l'utilisateur
//     $abonnementIds = $session->get('selected_abonnement_ids');
//     foreach ($abonnementIds as $abonnementId) {
//         $abonnement = $abonnementsRepository->find($abonnementId);
//         if (!$abonnement) {
//             continue;
//         }

//         $adhesion = new Adhesions();
//         $adhesion->setUser($this->getUser());
//         $adhesion->addAbonnement($abonnement);
//         $startDate = new \DateTime();
//         $endDate = $this->calculateEndDate($startDate, $abonnement->getDurée());
//         $adhesion->setDateDebut($startDate);
//         $adhesion->setDateFin($endDate);

//         $entityManager->persist($adhesion);
//     }

//     // Parcourir toutes les commandes
//     foreach ($orders as $order) {
//         $order->setIsPayer(false);
//         $order->setIsProcessed(true);

//         // Pour les abonnements des enfants
//         $kid = $order->getKid();
//         if ($kid) {
//             $kidId = $kid->getId();
//             $kidAbonnementAssociations = $session->get('kid_abonnement_associations', []);

//             if (array_key_exists($kidId, $kidAbonnementAssociations)) {
//                 $abonnementId = $kidAbonnementAssociations[$kidId];
//                 $abonnement = $abonnementsRepository->find($abonnementId);

//                 if ($abonnement) {
//                     $adhesion = new Adhesions();
//                     $adhesion->setUser($this->getUser());
//                     $adhesion->setKids($kid);
//                     $adhesion->addAbonnement($abonnement);
//                     $startDate = new \DateTime();
//                     $endDate = $this->calculateEndDate($startDate, $abonnement->getDurée());
//                     $adhesion->setDateDebut($startDate);
//                     $adhesion->setDateFin($endDate);

//                     $entityManager->persist($adhesion);
//                 }
//             }
//         }
//     }

//     $entityManager->flush();

//     return $this->render('stripe/success.html.twig');  // Renvoie vers un template pour le succès du paiement en espèces.
// }










}
