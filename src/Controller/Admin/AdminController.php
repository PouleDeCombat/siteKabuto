<?php

namespace App\Controller\Admin;

use DateTime;
use App\Entity\Users;
use App\Entity\Orders;
use DateTimeImmutable;
use App\Entity\Products;
use App\Entity\Adhesions;
use App\Form\OrderPaymentType;
use App\Form\RegistrationFormType;
use App\Form\CreateProductFormType;
use App\Form\UserAdminEditFormType;
use App\Repository\UsersRepository;
use App\Repository\OrdersRepository;
use App\Form\EditProductTypeFormType;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('adminBase.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/adherent', name: 'app_adherent')]
    public function userList(UsersRepository $usersRepository)
    {
        return $this->render('admin/adherent.html.twig', [
            'adherents' => $usersRepository->findAll(),
        ]);
    }


    #[Route('/admin/adherent/modifier/{id}', name: 'app_modifier')]
    public function adherentModification(Users $adherent, Request $request, EntityManagerInterface $entityManager)
    {
             

        $form = $this->createForm(UserAdminEditFormType::class, $adherent);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($adherent);
            $entityManager->flush();

            return $this->redirectToRoute('app_adherent');
        }

        return $this->render('admin/modifier.html.twig', [
            'form' => $form->createView(),
            'adherent' => $adherent,
        ]);
    }




    
     #[Route("/admin/commandes", name: "admin_orders")]
     
    public function orders(OrdersRepository $ordersRepository)
    {
        $orders = $ordersRepository->findAll();

        return $this->render('admin/orders.html.twig', [
            'orders' => $orders,
        ]);
    }



    #[Route("/admin/commandes/{id}/detail", name: "admin_order_detail")]
public function detail(Orders $order, Request $request, EntityManagerInterface $em)
{
    $form = $this->createForm(OrderPaymentType::class, $order);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();

        $this->addFlash('success', 'Méthode de paiement mise à jour avec succès!');
        return $this->redirectToRoute('admin_order_detail', ['id' => $order->getId()]);
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
    

    return $this->render('admin/order_detail.html.twig', [
        'order' => $order,
        'form' => $form->createView(),
        'sizes' => $sizes
    ]);
}





      #[Route("/admin/creation-produit", name:"app_create_product", methods:["GET", "POST"])]
     
    public function createProduct(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Products();
        $form = $this->createForm(CreateProductFormType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle the uploaded files if needed
            $product->setCreatedAt(new DateTimeImmutable('now'));
            
            $entityManager->persist($product);
            $entityManager->flush();

            // Add a flash message to show the product was created
            $this->addFlash('success', 'Product created successfully!');

            // Redirect to another page or list of products, for instance
            return $this->redirectToRoute('app_'); // Or any other route you prefer
        }

        return $this->render('admin/CreateProduct.html.twig', [
            'form' => $form->createView(),
        ]);
    }


 
    
     #[Route("/admin/list-des-produits", name:"app_product_index", methods:["GET"])]
     
    public function showProductList(ProductsRepository $productsRepository): Response
    {
        $products = $productsRepository->findAll();

        return $this->render('admin/productList.html.twig', [
            'products' => $products,
        ]);
    }


    
 #[Route("/product/{id}/delete", name:"app_product_delete", methods:["GET"])]
 
public function delete(int $id, ProductsRepository $productsRepository, EntityManagerInterface $entityManager): Response
{
    $product = $productsRepository->find($id);

    if (!$product) {
        $this->addFlash('error', 'Produit introuvable.');
        return $this->redirectToRoute('app_product_index');
    }

    foreach ($product->getOrdersDetails() as $orderDetail) {
        $entityManager->remove($orderDetail);
    }
    $entityManager->remove($product);
    $entityManager->flush();

    $this->addFlash('success', 'Produit supprimé avec succès.');

    return $this->redirectToRoute('app_product_index');
}


      #[Route("/product/{id}/edit", name:"app_product_edit", methods:["GET", "POST"])]
     
    public function edit(int $id, Request $request, ProductsRepository $productsRepository, EntityManagerInterface $entityManager): Response
    {
        $product = $productsRepository->find($id);

        if (!$product) {
            $this->addFlash('error', 'Produit introuvable.');
            return $this->redirectToRoute('app_product_index');
        }

        $form = $this->createForm(EditProductTypeFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Produit mis à jour avec succès!');
            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('admin/editProduct.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }



     
    #[Route("/admin/user/{id}", name:"app_user_details")]
    public function userDetails(int $id, UsersRepository $userRepository, EntityManagerInterface $em): Response
    {
        $user = $userRepository->find($id);
    
        if (!$user) {
            throw $this->createNotFoundException('L\'utilisateur demandé n\'existe pas.');
        }
    
        $subscriptions = $em->getRepository(Adhesions::class)->findBy(['user' => $user]);
    
        return $this->render('admin/userDetails.html.twig', [
            'user' => $user,
            'subscriptions' => $subscriptions,
        ]);
    }
    

}
 







      








