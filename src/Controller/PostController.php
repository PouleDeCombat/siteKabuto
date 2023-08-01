<?php

namespace App\Controller;
use App\Entity\Post;
use App\Entity\Products;
use app\Entity\Competiteurs;
use App\Form\EditProfileType;

use Doctrine\ORM\EntityManager;
use App\Repository\PostRepository;
use App\Repository\UsersRepository;
use App\Repository\ProductsRepository;
use App\Form\EditCompetiteurProfileType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetiteursRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PostController extends AbstractController
{
    #[Route('/', name: 'app_landing')]
    public function renderLanding(): Response
    {

        return $this->render('pages/index.html.twig');
    }


  


    #[Route('/coaches', name: 'app_coaches')]
    public function renderCoach()
    {
        return $this->render('pages/coaches.html.twig');
    }

    #[Route('/disciplines', name: 'app_disciplines')]
    public function renderDiscipline()
    {
        return $this->render('pages/disciplines.html.twig');
    }

    #[Route('/horaire', name: 'app_horaire')]
    public function renderHoraire()
    {
        return $this->render('pages/horaire.html.twig');
    }

    #[Route('/combattant', name: 'app_combattant')]
    public function renderCombattant()
    {
        return $this->render('pages/combattant.html.twig');
    }

    #[Route('/boutique', name: 'app_boutique')]
    public function renderBoutique(EntityManagerInterface $entityManager)
    {
        $product = $entityManager->getRepository(Products::class)->findAll();
        return $this->render('pages/boutique.html.twig', [
            'product' => $product
        ]);
    }

    #[Route('', name: 'liste_produit')]
    public function productList(ProductsRepository $productsRepository )
    {
        return $this->render('pages/boutique.html.twig', [
            'products' => $productsRepository->findAll(),
        ]);
    }
    

    #[Route('/tarif', name: 'app_tarif')]
    public function renderTarif()
    {
        return $this->render('pages/tarif.html.twig');
    }

    #[Route('/profile', name: 'app_profile')]
    public function renderProfile(): Response
    {
        
        return $this->render('usersPages/profile.html.twig');
    }

    #[Route('/profile/modifier', name: 'app_edit_profile')]
    public function renderEditProfile(Request $request, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
       
            $em->persist($user);
            $em->flush();

            $this->addFlash('message', 'Profil mis à jour');
            return $this->redirectToRoute('app_profile');

        }
        return $this->render('usersPages/editprofile.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/password/modifier', name: 'app_edit_password')]
    public function renderEditPassword(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em)
    {
        if($request->isMethod('POST')){
            

            $user = $this->getUser();
            //Verifier si les deux mdp sont identiques
            if($request->request->get('password') == $request->get('password2')){
                $user->setPassword($userPasswordHasher->hashPassword($user, $request->get('password')));
                $em->flush();
                $this->addFlash('message', 'Mot de passe mis à jour avec succès' );

                return $this->redirectToRoute('app_profile');
            } else {
                $this->addFlash('error', 'Les deux mots de passe ne sont pas identiques');

            }

        }
        
        return $this->render('usersPages/editpassword.html.twig');

    }

    #[Route('/profile/competiteur', name: 'app_profile_competiteur')]
    public function renderEditProfileCompetiteur(Request $request, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        $form = $this->createForm(EditCompetiteurProfileType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
       
            $em->persist($user);
            $em->flush();

            $this->addFlash('message', 'Profil mis à jour');
            return $this->redirectToRoute('app_profile');

        }
        return $this->render('usersPages/editcompetiteur.html.twig',[
            'form' => $form->createView(),
        ]);
    }
    
}