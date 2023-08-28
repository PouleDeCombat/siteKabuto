<?php
 
namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Orders;
use App\Entity\Adhesions;
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
    

    foreach ($panier as $id => $quantity) {
        $product = $productsRepository->find($id);
        $orderDetailsList = $ordersDetailsRepository->findBy(['products' => $product]);

        foreach ($orderDetailsList as $orderDetail) {
            if ($orderDetail instanceof OrdersDetails) {
                $size = $orderDetail->getSize();
                $key = $id . '_' . $size;

                if (!isset($data[$key])) {
                    $data[$key] = [
                        'product' => $product,
                        'quantity' => 0, // initialise à 0 et sera incrémenté plus bas
                        'size' => $size
                    ];
                }

                // Incremente la quantité
                $data[$key]['quantity'] += $quantity;
            }
        }
        $total += $product->getPrice() * $quantity;
    }

    $data = array_values($data);  // Réindexer pour avoir un tableau normal

    return $this->render('stripe/index.html.twig', [
        'stripe_key' => $_ENV["STRIPE_KEY"],
        'data' => $data,
        'total' => $total
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
        return $this->redirectToRoute('app_home');
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
  
    $lineItems = [[
        'price_data' => [
            'currency' => 'eur',
            'product_data' => [
                'name' => 'Votre abonnement pour enfants',  // change the name as per your requirement
            ],
            'unit_amount' => $order->getTotal() * 100, 
        ],
        'quantity' => 1, 
    ]];
    
    $checkout_session = Session::create([
        'payment_method_types' => ['card'],
        // 'customer_email' => $order->getUsers()->getEmail(),
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => $generator->generate('payment_kids_success', [], UrlGeneratorInterface::ABSOLUTE_URL),  // change the route as per your requirement
        'cancel_url' => $generator->generate('payment_kids_error', [], UrlGeneratorInterface::ABSOLUTE_URL),    // change the route as per your requirement
    ]);

    return new RedirectResponse($checkout_session->url);
}


#[Route('/stripe/kids-success', name: 'payment_kids_success')]
public function stripeKidsSuccess(EntityManagerInterface $entityManager):Response
{
    return $this->render('stripe/success.html.twig');
}

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
            return $this->redirectToRoute('app_home');
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
public function stripeSuccess(Request $request,EntityManagerInterface $entityManager,$orderId):Response
{
    $orderId = $request->get('orderId');
    $order = $entityManager->getRepository(Orders::class)->findOneBy(['id' => $orderId]);
    $order->setIsPayer(true);

    $entityManager->flush();

     $adhesion = new Adhesions();
     $adhesion->setUser($order->getUsers()); 
     $adhesion->setDateDebut(new \DateTime()); 

     $abonnement = $order->getAbonnement()->first(); 
     $duration = $abonnement->getDurée();
     $endDate = new \DateTime();
     if ($duration === '1 MOIS') {
         $endDate->modify('+1 month');
     } elseif ($duration === '3 MOIS') {
         $endDate->modify('+3 months');
     } elseif ($duration === 'ANNUEL') {
         $endDate->modify('+1 year');
     }
     $adhesion->setDateFin($endDate);
 
     foreach ($order->getAbonnement() as $abonnement) {
        $abonnement->addAdhesion($adhesion);
    }
 
     $entityManager->persist($adhesion);
     $entityManager->flush();


 
    return $this->render('stripe/success.html.twig');
}












}
