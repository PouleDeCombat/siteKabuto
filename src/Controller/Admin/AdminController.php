<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Entity\Orders;
use App\Form\OrderPaymentType;
use App\Form\RegistrationFormType;
use App\Form\UserAdminEditFormType;
use App\Repository\UsersRepository;
use App\Repository\OrdersRepository;
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


    

    return $this->render('admin/order_detail.html.twig', [
        'order' => $order,
        'form' => $form->createView()
    ]);
}
 







      







}
