<?php
namespace App\Controller;

use Exception;
use App\Entity\Orders;
use App\Entity\Products;
use App\Entity\OrdersDetails;
use App\Repository\UsersRepository;
use App\Repository\OrdersRepository;
use App\Repository\ProductsRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// #[Route('/panier', name:'cart_')]
// class CartController extends AbstractController
// {
        
    

//     #[Route('/', name: 'index')]
//     public function index(SessionInterface $session, ProductsRepository $productsRepository, EntityManagerInterface $em)
//     {
//         $panier = $session->get('panier', []);
//         $data = [];
//         $total = 0;
    
//         foreach ($panier as $id => $quantity) {
//             $product = $productsRepository->find($id);
            
//             // Récupérez les détails de la commande
//             $orderDetail = $em->getRepository(OrdersDetails::class)->findOneBy(['products' => $product]);
            
//             $data[] = [
//                 'product' => $product,
//                 'quantity' => $quantity,
//                 'size' => $orderDetail ? $orderDetail->getSize() : null // Si orderDetail existe, récupérez la taille, sinon mettez null
//             ];
//             $total += $product->getPrice() * $quantity;
//         }
        
    
//         return $this->render('panier/index.html.twig', compact('data', 'total'));
//     }
    








// #[Route('/add/{id}', name: 'addtocart')]
// public function add(Products $product, Request $request, SessionInterface $session, EntityManagerInterface $em, UsersRepository $userRepository, ProductsRepository $productsRepository)
// {
//     $id = $product->getId();
//     $prod = $productsRepository->find($id);

//     $user = $this->getUser();
//     if (!$user) {
//         return $this->redirectToRoute('app_login');
//     }

//     $panier = $session->get('panier', []);

//     if (empty($panier[$id])) {
//         $panier[$id] = 1;
//     } else {
//         $panier[$id]++;
//     }

//     $session->set('panier', $panier);

//     $totalPrice = 0;
//     foreach ($panier as $id => $quantity) {
//         $product = $productsRepository->find($id);
//         if (!$product) {
//             continue;
//         }
//         $totalPrice += $product->getPrice() * $quantity;
//     }

//     $user = $userRepository->findOneBy(['id' => $this->getUser()->getId()]);
//     if (!$user) {
//         return $this->redirectToRoute('app_login');
//     }

//     $orderExist = $em->getRepository(Orders::class)->findOneBy(['Users' => $user, 'isPayer' => false]);

//     if (!$orderExist) {
//         $order = new Orders();
//         $order->setTotal($totalPrice);
//         $order->setUsers($user);
//         $order->setCreatedAt(new \DateTimeImmutable('now'));
//         $order->setIsPayer(false);
//         $order->setIsProcessed(false);
//         $reference = 'ORD-' . $user->getId() . '-' . time();
//         $order->setReference($reference);

//         $em->persist($order);
//         $em->flush();
//     } else {
//         $order = $orderExist;
//     }

//     $orderTotal = $em->getRepository(OrdersDetails::class)->findBy(['orders' => $order]);

//     $total = 0;
//     foreach ($orderTotal as $rez) {
//         $total += $rez->getPrice() * $rez->getQuantity();
//     }

//     $order->setTotal($total);
//     $em->persist($order);
//     $em->flush();

//     // Récupérez la taille depuis la requête
//     $size = $request->get('size');
// if (!$size) {
//     // Traitez l'erreur : redirigez l'utilisateur, affichez un message d'erreur, etc.
//     // Par exemple, redirigez vers la page du produit avec un message d'erreur.
//     $this->addFlash('error', 'Veuillez sélectionner une taille.');
//     return $this->redirectToRoute('products_details', ['slug' => $product->getSlug(), 'id' => $product->getId()]);
// }




//     $orderDetailsExist = $em->getRepository(OrdersDetails::class)->findOneBy(['orders' => $order, 'products' => $prod, 'size' => $size]);

//     if (!$orderDetailsExist) {
//         $orderDetails = new OrdersDetails();
//         $orderDetails->setQuantity(1);
//         $orderDetails->setPrice($prod->getPrice());
//         $orderDetails->setOrders($order);
//         $orderDetails->setProducts($prod);
//         $orderDetails->setSize($size);  // Ajout de la taille

//         $em->persist($orderDetails);
//         $em->flush();
//     } else {
//         $orderDetails = $orderDetailsExist;
//         $orderDetails->setQuantity($orderDetails->getQuantity() + 1);
//         $em->persist($orderDetails);
//         $em->flush();

//         $panier = $session->get('panier', []);
//         $panier[$product->getId()] = $orderDetails->getQuantity();
//         $session->set('panier', $panier);

//     }

//     $orderTotal = $em->getRepository(OrdersDetails::class)->findBy(['orders' => $order]);

//     $total = 0;
//     foreach ($orderTotal as $rez) {
//         $total += $rez->getPrice() * $rez->getQuantity();
//     }

//     $order->setTotal($total);
//     $em->persist($order);
//     $em->flush();

//     return $this->redirectToRoute('cart_index');
// }



// #[Route('/increase/{id}', name: 'increase_quantity')]
// public function increaseQuantity(Products $product, EntityManagerInterface $em, SessionInterface $session)
// {
//     $user = $this->getUser();
//     if (!$user) {
//         return $this->redirectToRoute('app_login');
//     }
    
//     $orderExist = $em->getRepository(Orders::class)->findOneBy(['Users' => $user, 'isPayer' => false]);
//     if (!$orderExist) {
//         // Si l'utilisateur n'a pas de commande en cours, redirigez-le vers la page d'accueil du panier.
//         return $this->redirectToRoute('cart_index');
//     }

//     // Récupérez les détails de la commande pour le produit spécifique
//     $orderDetailsExist = $em->getRepository(OrdersDetails::class)->findOneBy(['orders' => $orderExist, 'products' => $product]);
//     if (!$orderDetailsExist) {
//         // Si le produit n'est pas dans le panier, redirigez vers la page d'accueil du panier.
//         return $this->redirectToRoute('cart_index');
//     }
   
//     // Augmenter la quantité du produit
//     $orderDetailsExist->setQuantity($orderDetailsExist->getQuantity() + 1);
//     $em->persist($orderDetailsExist);
//     $em->flush();

//      // Calculez le nouveau total pour la commande après avoir augmenté la quantité
//      $total = 0;
//      foreach ($orderExist->getOrdersDetails() as $detail) {
//          $total += $detail->getPrice() * $detail->getQuantity();
//      }
//      $orderExist->setTotal($total);
//      $em->persist($orderExist);
//      $em->flush();

//     $panier = $session->get('panier', []);
//     $panier[$product->getId()] = $orderDetailsExist->getQuantity();
//     $session->set('panier', $panier);

    
//     return $this->redirectToRoute('cart_index');
// }




// public function addOrderDetailWithDifferentSize(EntityManagerInterface $em, $currentOrderId, $currentProductId, $newSize)
// {
//     // 1. Récupérez l'entité OrdersDetails du produit actuel que vous souhaitez dupliquer
//     $currentOrderDetail = $em->getRepository(OrdersDetails::class)->findOneBy([
//         'orders' => $currentOrderId,
//         'products' => $currentProductId
//     ]);

//     if (!$currentOrderDetail) {
//         throw new Exception("OrderDetail not found!");
//     }

//     // 2. Créez une nouvelle instance de l'entité OrdersDetails
//     $newOrderDetail = new OrdersDetails();

//     // 3. Copiez toutes les propriétés
//     $newOrderDetail->setQuantity($currentOrderDetail->getQuantity());
//     $newOrderDetail->setPrice($currentOrderDetail->getPrice());
//     $newOrderDetail->setOrders($currentOrderDetail->getOrders());
//     $newOrderDetail->setProducts($currentOrderDetail->getProducts());

//     // 4. Modifiez la taille
//     $newOrderDetail->setSize($newSize);

//     // 5. Persistez et flush
//     $em->persist($newOrderDetail);
//     $em->flush();
// }






//     #[Route('/remove/{id}', name: 'remove')]
//     public function remove(Products $product, SessionInterface $session, EntityManagerInterface $em)
//     {
//         $id = $product->getId();
    
//         $panier = $session->get('panier', []);
    
//         // Check if product exists in the cart and its quantity is more than 1
//         if(!empty($panier[$id]) && $panier[$id] > 1){
//             $panier[$id]--;
//         } else {
//             unset($panier[$id]);
//         }
    
//         $session->set('panier', $panier);
    
//         $user = $this->getUser();
//         $order = $em->getRepository(Orders::class)->findOneBy(['Users' => $user, 'isPayer' => false]);
    
//         if($order){
//             $orderDetails = $em->getRepository(OrdersDetails::class)->findOneBy(['orders' => $order, 'products' => $product]);
    
//             if($orderDetails){
//                 if($orderDetails->getQuantity() > 1){
//                     $orderDetails->setQuantity($orderDetails->getQuantity()-1);
//                 }else{
//                     $em->remove($orderDetails);
//                 }
//                 $em->flush();
//                 // Recalculate the total for the order
//                 $this->updateOrderTotal($em, $order);
//             }
//         }
        
//         return $this->redirectToRoute('cart_index');
//     }
    
//     #[Route('/delete/{id}', name: 'delete')]
//     public function delete(Products $product, SessionInterface $session, EntityManagerInterface $em)
//     {
//         $id = $product->getId();
    
//         $panier = $session->get('panier', []);
    
//         if(!empty($panier[$id])){
//             unset($panier[$id]);
//         }
    
//         $session->set('panier', $panier);
    
//         $user = $this->getUser();
//         $order = $em->getRepository(Orders::class)->findOneBy(['Users' => $user, 'isPayer' => false]);
    
//         if($order){
//             $orderDetails = $em->getRepository(OrdersDetails::class)->findBy(['orders' => $order, 'products' => $product]);
    
//             if($orderDetails){
//                 foreach($orderDetails as $detail){
//                     $em->remove($detail);
//                 }
//                 $em->flush();
//                 // Recalculate the total for the order
//                 $this->updateOrderTotal($em, $order);
//             }
//         }
    
//         return $this->redirectToRoute('cart_index');
//     }
    


//     private function updateOrderTotal(EntityManagerInterface $em, Orders $order){
//         // Get all order details for the current order
//         $orderDetails = $em->getRepository(OrdersDetails::class)->findBy(['orders' => $order]);
    
//         $total = 0;
    
//         // Calculate the new total
//         foreach($orderDetails as $details){
//             $total += $details->getPrice() * $details->getQuantity();
//         }
    
//         // Set and persist the new total
//         $order->setTotal($total);
//         $em->persist($order);
//         $em->flush();
//     }
    


//     #[Route('/empty', name: 'empty')]
// public function empty(SessionInterface $session, EntityManagerInterface $em)
// {
//     $session->remove('panier');

//     $user = $this->getUser();

//     if($user){
//         $order = $em->getRepository(Orders::class)->findOneBy(['Users' => $user, 'isPayer' => false]);

//         if($order){
//             $orderDetails = $em->getRepository(OrdersDetails::class)->findBy(['orders' => $order]);

//             if($orderDetails){
//                 foreach($orderDetails as $detail){
//                     $em->remove($detail);
//                 }
//                 $em->flush();
//                 // As we're emptying the cart, the total should be 0.
//                 $order->setTotal(0);
//                 $em->persist($order);
//                 $em->flush();
//             }
//         }
//     }

//     return $this->redirectToRoute('cart_index');
// }




// }


#[Route('/panier', name:'cart_')]
class CartController extends AbstractController
{
        
    

    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, ProductsRepository $productsRepository, EntityManagerInterface $em)
    {
        $panier = $session->get('panier', []);
        $data = [];
        $total = 0;
    
        foreach ($panier as $key => $quantity) {
            $parts = explode('-', $key);
            if (count($parts) < 2) {
                // La clé n'est pas correctement formatée, ignorez cette entrée
                continue;
            }
            list($id, $size) = $parts;
            $product = $productsRepository->find($id);
            if (!$product) {
    // L'ID du produit n'a pas été trouvé dans la base de données
    // Vous pouvez ignorer ce tour de boucle ou gérer cette situation différemment
    continue;
}
            
            // Récupérez les détails de la commande
            $orderDetail = $em->getRepository(OrdersDetails::class)->findOneBy(['products' => $product, 'size' => $size]);

            
            $data[] = [
                'product' => $product,
                'quantity' => $quantity,
                'size' => $size
            ];
            $total += $product->getPrice() * $quantity;
        }
        
        return $this->render('panier/index.html.twig', compact('data', 'total'));
    }
    








    #[Route('/add/{id}', name: 'addtocart')]
    public function add(Products $product, Request $request, SessionInterface $session, EntityManagerInterface $em, UsersRepository $userRepository, ProductsRepository $productsRepository)
    {
        $size = $request->get('size');
        if (!$size) {
            $this->addFlash('error', 'Veuillez sélectionner une taille.');
            return $this->redirectToRoute('products_details', ['slug' => $product->getSlug(), 'id' => $product->getId()]);
        }
        
        // Utilisation de la clé unique pour le panier
        $key = $product->getId() . '-' . $size;

        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $panier = $session->get('panier', []);

        if (empty($panier[$key])) {
            $panier[$key] = 1;
        } else {
            $panier[$key]++;
        }

        $session->set('panier', $panier);

    $totalPrice = 0;
    foreach ($panier as $id => $quantity) {
        $product = $productsRepository->find($id);
        if (!$product) {
            continue;
        }
        $totalPrice += $product->getPrice() * $quantity;
    }

    $user = $userRepository->findOneBy(['id' => $this->getUser()->getId()]);
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    $orderExist = $em->getRepository(Orders::class)->findOneBy(['Users' => $user, 'isPayer' => false]);

    if (!$orderExist) {
        $order = new Orders();
        $order->setTotal($totalPrice);
        $order->setUsers($user);
        $order->setCreatedAt(new \DateTimeImmutable('now'));
        $order->setIsPayer(false);
        $order->setIsProcessed(false);
        $reference = 'ORD-' . $user->getId() . '-' . time();
        $order->setReference($reference);

        $em->persist($order);
        $em->flush();
    } else {
        $order = $orderExist;
    }

    $orderTotal = $em->getRepository(OrdersDetails::class)->findBy(['orders' => $order]);

    $total = 0;
    foreach ($orderTotal as $rez) {
        $total += $rez->getPrice() * $rez->getQuantity();
    }

    $order->setTotal($total);
    $em->persist($order);
    $em->flush();

    // Récupérez la taille depuis la requête
    $size = $request->get('size');
if (!$size) {
    // Traitez l'erreur : redirigez l'utilisateur, affichez un message d'erreur, etc.
    // Par exemple, redirigez vers la page du produit avec un message d'erreur.
    $this->addFlash('error', 'Veuillez sélectionner une taille.');
    return $this->redirectToRoute('products_details', ['slug' => $product->getSlug(), 'id' => $product->getId()]);
}




    $orderDetailsExist = $em->getRepository(OrdersDetails::class)->findOneBy(['orders' => $order, 'products' => $product, 'size' => $size]);

    if (!$orderDetailsExist) {
        $orderDetails = new OrdersDetails();
        $orderDetails->setQuantity(1);
        $orderDetails->setPrice($product->getPrice());
        $orderDetails->setOrders($order);
        $orderDetails->setProducts($product);
        $orderDetails->setSize($size);  // Ajout de la taille

        $em->persist($orderDetails);
        $em->flush();
    } else {
        $orderDetails = $orderDetailsExist;
        $orderDetails->setQuantity($orderDetails->getQuantity() + 1);
        $em->persist($orderDetails);
        $em->flush();

        $panier = $session->get('panier', []);
        $panier[$product->getId()] = $orderDetails->getQuantity();
        $session->set('panier', $panier);

    }

    $orderTotal = $em->getRepository(OrdersDetails::class)->findBy(['orders' => $order]);

    $total = 0;
    foreach ($orderTotal as $rez) {
        $total += $rez->getPrice() * $rez->getQuantity();
    }

    $order->setTotal($total);
    $em->persist($order);
    $em->flush();

    return $this->redirectToRoute('cart_index');
}



#[Route('/increase/{id}/{size}', name: 'increase_quantity')]
public function increaseQuantity(Products $product, $size, EntityManagerInterface $em, SessionInterface $session)
{
    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
    
    $orderExist = $em->getRepository(Orders::class)->findOneBy(['Users' => $user, 'isPayer' => false]);
    if (!$orderExist) {
        // Si l'utilisateur n'a pas de commande en cours, redirigez-le vers la page d'accueil du panier.
        return $this->redirectToRoute('cart_index');
    }

    // Récupérez les détails de la commande pour le produit spécifique
    $orderDetailsExist = $em->getRepository(OrdersDetails::class)->findOneBy(['orders' => $orderExist, 'products' => $product, 'size' => $size]);
    if (!$orderDetailsExist) {
        // Si le produit n'est pas dans le panier, redirigez vers la page d'accueil du panier.
        return $this->redirectToRoute('cart_index');
    }
   
    // Augmenter la quantité du produit
    $orderDetailsExist->setQuantity($orderDetailsExist->getQuantity() + 1);
    $em->persist($orderDetailsExist);
    $em->flush();

     // Calculez le nouveau total pour la commande après avoir augmenté la quantité
     $total = 0;
     foreach ($orderExist->getOrdersDetails() as $detail) {
         $total += $detail->getPrice() * $detail->getQuantity();
     }
     $orderExist->setTotal($total);
     $em->persist($orderExist);
     $em->flush();

     $panierKey = $product->getId() . '-' . $size;
     $panier[$panierKey] = $orderDetailsExist->getQuantity();
    $session->set('panier', $panier);

    
    return $this->redirectToRoute('cart_index');
}




public function addOrderDetailWithDifferentSize(EntityManagerInterface $em, $currentOrderId, $currentProductId, $newSize)
{
    // 1. Récupérez l'entité OrdersDetails du produit actuel que vous souhaitez dupliquer
    $currentOrderDetail = $em->getRepository(OrdersDetails::class)->findOneBy([
        'orders' => $currentOrderId,
        'products' => $currentProductId
    ]);

    if (!$currentOrderDetail) {
        throw new Exception("OrderDetail not found!");
    }

    // 2. Créez une nouvelle instance de l'entité OrdersDetails
    $newOrderDetail = new OrdersDetails();

    // 3. Copiez toutes les propriétés
    $newOrderDetail->setQuantity($currentOrderDetail->getQuantity());
    $newOrderDetail->setPrice($currentOrderDetail->getPrice());
    $newOrderDetail->setOrders($currentOrderDetail->getOrders());
    $newOrderDetail->setProducts($currentOrderDetail->getProducts());

    // 4. Modifiez la taille
    $newOrderDetail->setSize($newSize);

    // 5. Persistez et flush
    $em->persist($newOrderDetail);
    $em->flush();
}






    #[Route('/remove/{id}/{size}', name: 'remove')]
    public function remove(Products $product,$size, SessionInterface $session, EntityManagerInterface $em)
    {
        
        $panierKey = $product->getId() . '-' . $size;
    
        $panier = $session->get('panier', []);
    
        // Check if product exists in the cart and its quantity is more than 1
        if(!empty($panier[$panierKey]) && $panier[$panierKey] > 1){
            $panier[$panierKey]--;
        } else {
            unset($panier[$panierKey]);
        }
    
        $session->set('panier', $panier);
    
        $user = $this->getUser();
        $order = $em->getRepository(Orders::class)->findOneBy(['Users' => $user, 'isPayer' => false]);
    
        if($order){
            $orderDetails = $em->getRepository(OrdersDetails::class)->findOneBy(['orders' => $order, 'products' => $product, 'size' => $size]);
    
            if($orderDetails){
                if($orderDetails->getQuantity() > 1){
                    $orderDetails->setQuantity($orderDetails->getQuantity()-1);
                }else{
                    $em->remove($orderDetails);
                }
                $em->flush();
                // Recalculate the total for the order
                $this->updateOrderTotal($em, $order);
            }
        }
        
        return $this->redirectToRoute('cart_index');
    }
    
    #[Route('/delete/{id}/{size}', name: 'delete')]
    public function delete(Products $product, $size, SessionInterface $session, EntityManagerInterface $em)
    {
        $panierKey = $product->getId() . '-' . $size;
    
    $panier = $session->get('panier', []);
    
    if(!empty($panier[$panierKey])){
        unset($panier[$panierKey]);
    }
    
        $session->set('panier', $panier);
    
        $user = $this->getUser();
        $order = $em->getRepository(Orders::class)->findOneBy(['Users' => $user, 'isPayer' => false]);
    
        if($order){
            $orderDetails = $em->getRepository(OrdersDetails::class)->findBy(['orders' => $order, 'products' => $product, 'size' => $size]);
    
            if($orderDetails){
                foreach($orderDetails as $detail){
                    $em->remove($detail);
                }
                $em->flush();
                // Recalculate the total for the order
                $this->updateOrderTotal($em, $order);
            }
        }
    
        return $this->redirectToRoute('cart_index');
    }
    


    private function updateOrderTotal(EntityManagerInterface $em, Orders $order){
        // Get all order details for the current order
        $orderDetails = $em->getRepository(OrdersDetails::class)->findBy(['orders' => $order]);
    
        $total = 0;
    
        // Calculate the new total
        foreach($orderDetails as $details){
            $total += $details->getPrice() * $details->getQuantity();
        }
    
        // Set and persist the new total
        $order->setTotal($total);
        $em->persist($order);
        $em->flush();
    }
    


    #[Route('/empty', name: 'empty')]
public function empty(SessionInterface $session, EntityManagerInterface $em)
{
    $session->remove('panier');

    $user = $this->getUser();

    if($user){
        $order = $em->getRepository(Orders::class)->findOneBy(['Users' => $user, 'isPayer' => false]);

        if($order){
            $orderDetails = $em->getRepository(OrdersDetails::class)->findBy(['orders' => $order]);

            if($orderDetails){
                foreach($orderDetails as $detail){
                    $em->remove($detail);
                }
                $em->flush();
                // As we're emptying the cart, the total should be 0.
                $order->setTotal(0);
                $em->persist($order);
                $em->flush();
            }
        }
    }

    return $this->redirectToRoute('cart_index');
}




}