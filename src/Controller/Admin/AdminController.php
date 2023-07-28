<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Form\UserAdminEditFormType;
use App\Repository\UsersRepository;
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




      







}
