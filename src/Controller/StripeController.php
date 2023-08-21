<?php
 
namespace App\Controller;

use Stripe;
use App\Entity\OrdersDetails;
use App\Repository\ProductsRepository;
use App\Repository\AbonnementsRepository;
use App\Repository\OrdersDetailsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 
class StripeController extends AbstractController
{
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

}