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
use App\Repository\ProductsRepository;
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
public function stripeCheckOut(EntityManagerInterface $entityManager,$orderId, UrlGeneratorInterface $generator): RedirectResponse
{


    $orderRepository = $entityManager->getRepository(Orders::class);
    $order = $orderRepository->findOneBy(['id' => $orderId]);

    if(!$order){
        return $this->redirectToRoute('app_landing');
    }

    \Stripe\Stripe::setApiKey('sk_test_51NZyE0ECy2gfABuUGrCGxxTBYM2h9hzbF4Qwt9vpWEPBkNB2KrYyfg5txidH37JqfrORvoR2Y6iA33JRaE1bPR9i00V1QED7kU');
  

    $lineItems = [[
        'price_data' => [
            'currency' => 'eur',
            'product_data' => [
                'name' => 'Votre abonnement', 
            ],
            'unit_amount' => $order->getTotal() * 100, 
        ],
        'quantity' => 1, 
    ]];
    
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
public function stripeKidsCheckOut(EntityManagerInterface $entityManager, $orderId, UrlGeneratorInterface $generator): RedirectResponse
{
    $orderRepository = $entityManager->getRepository(Orders::class);
    $order = $orderRepository->findOneBy(['id' => $orderId]);

    if(!$order){
        return $this->redirectToRoute('app_home');
    }

    
    \Stripe\Stripe::setApiKey('sk_test_51NZyE0ECy2gfABuUGrCGxxTBYM2h9hzbF4Qwt9vpWEPBkNB2KrYyfg5txidH37JqfrORvoR2Y6iA33JRaE1bPR9i00V1QED7kU');

    $totalPrice = 0;
$orders = $entityManager->getRepository(Orders::class)->findBy(['Users' => $this->getUser(), 'isPayer' => false]);
foreach ($orders as $order) {
    $totalPrice += $order->getTotal();
}
  
    $lineItems = [[
        'price_data' => [
            'currency' => 'eur',
            'product_data' => [
                'name' => 'Votre abonnement pour enfants',  // change the name as per your requirement
            ],
            'unit_amount' => $totalPrice * 100, 
        ],
        'quantity' => 1, 
    ]];
    
    $checkout_session = Session::create([
        'payment_method_types' => ['card'],
        // 'customer_email' => $order->getUsers()->getEmail(),
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => $generator->generate('payment_kids_success', ['orderId' => $orderId], UrlGeneratorInterface::ABSOLUTE_URL),  // change the route as per your requirement
        'cancel_url' => $generator->generate('payment_kids_error', ['orderId' => $orderId], UrlGeneratorInterface::ABSOLUTE_URL),    // change the route as per your requirement
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







#[Route('/stripe/checkout-session/{orderId}/{type}', name: 'payment_stripe_checkout')]
    public function bothStripeCheckOut(EntityManagerInterface $entityManager, $orderId, $type, UrlGeneratorInterface $generator): RedirectResponse
    {
        $orderRepository = $entityManager->getRepository(Orders::class);
        $order = $orderRepository->findOneBy(['id' => $orderId]);

        if (!$order) {
            return $this->redirectToRoute('app_landing');
        }

        \Stripe\Stripe::setApiKey('sk_test_51NZyE0ECy2gfABuUGrCGxxTBYM2h9hzbF4Qwt9vpWEPBkNB2KrYyfg5txidH37JqfrORvoR2Y6iA33JRaE1bPR9i00V1QED7kU');

        $productName = ($type == 'kids') ? 'Votre abonnement pour enfants' : 'Votre abonnement';

        $lineItems = [[
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $productName,
                ],
                'unit_amount' => $order->getTotal() * 100,
            ],
            'quantity' => 1,
        ]];

        $successRoute = ($type == 'kids') ? 'payment_kids_success' : 'payment_success';
        $errorRoute = ($type == 'kids') ? 'payment_kids_error' : 'payment_error';

        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $generator->generate($successRoute, ['orderId' => $orderId], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $generator->generate($errorRoute, ['orderId' => $orderId], UrlGeneratorInterface::ABSOLUTE_URL),
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
    
            // Gestion de la date de fin
            $duration = $abonnement->getDurée();
            $now = new \DateTime();
$endDate = clone $now; // Créez une copie de la date actuelle

switch ($duration) {
    case '1 MOIS':
        $endDate->modify('+1 month');
        break;
    case '3 MOIS':
        $endDate->modify('+3 months');
        break;
    case 'ANNUEL':
        $juneEnd = new \DateTime('this year-06-30');
        if ($now <= $juneEnd) {
            $endDate = $juneEnd;
        } else {
            $endDate = new \DateTime('next year-06-30');
        }
        break;
    default:
        // Gérer une valeur inattendue pour $duration, si nécessaire
        throw new \Exception("Duration value not recognized: $duration");
}

    
            $adhesion->setDateDebut(new \DateTime()); // Si vous souhaitez également définir la date de début à "aujourd'hui".
            $adhesion->setDateFin($endDate);
    
            $entityManager->persist($adhesion);
        }
    
        // Validez toutes les adhésions que nous venons de créer.
        $entityManager->flush();
    
        // Redirigez l'utilisateur vers une page de succès ou là où vous le souhaitez.
        return $this->render('stripe/success.html.twig');
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
        $order->setIsProcessed(false);

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
            $adhesion->setUser($user);
            $adhesion->addAbonnement($abonnement);

            // Gestion de la date de fin
            $duration = $abonnement->getDurée();
            $now = new \DateTime();
            $endDate = clone $now;

            switch ($duration) {
                case '1 MOIS':
                    $endDate->modify('+1 month');
                    break;
                case '3 MOIS':
                    $endDate->modify('+3 months');
                    break;
                case 'ANNUEL':
                    $juneEnd = new \DateTime('this year-06-30');
                    if ($now <= $juneEnd) {
                        $endDate = $juneEnd;
                    } else {
                        $endDate = new \DateTime('next year-06-30');
                    }
                    break;
                default:
                    throw new \Exception("Duration value not recognized: $duration");
            }

            $adhesion->setDateDebut(new \DateTime());
            $adhesion->setDateFin($endDate);

            $entityManager->persist($adhesion);
        }

        $entityManager->flush();

        return $this->render('stripe/success.html.twig');
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
    
                // Calculer la date de fin en fonction de la durée d'abonnement
                $duration = $abonnement->getDurée();
                $now = new \DateTime();
                $endDate = clone $now;
    
                $durations = [
                    '1 MOIS' => '+1 month',
                    '3 MOIS' => '+3 months',
                    'ANNUEL' => ($now <= new \DateTime('this year-06-30')) ? 'this year-06-30' : 'next year-06-30',
                ];
    
                if (!array_key_exists($duration, $durations)) {
                    throw new \Exception("Duration value not recognized: $duration");
                }
    
                if ($duration === 'ANNUEL') {
                    $endDate = new \DateTime($durations[$duration]);
                } else {
                    $endDate->modify($durations[$duration]);
                }
    
                // Définir les dates de début et de fin de l'adhésion
                $adhesion->setDateDebut(new \DateTime());
                $adhesion->setDateFin($endDate);
    
                // Autres traitements de l'objet Adhesion si nécessaire ...
    
                $entityManager->persist($adhesion);
            }
        }
    }
    
    $entityManager->flush();
    
    return $this->render('stripe/success.html.twig');
}
    
    
    
    
    


    

    












}
