<?php

namespace App\Controller;
use App\Entity\Post;
use App\Entity\Users;
use App\Entity\Products;
use app\Entity\Competiteurs;

use App\Form\EditProfileType;
use Doctrine\ORM\EntityManager;
use App\Form\EditKidsProfileType;
use App\Repository\KidsRepository;
use App\Repository\PostRepository;
use App\Repository\UsersRepository;
use App\Repository\ProductsRepository;
use App\Form\EditCompetiteurProfileType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetiteursRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Form\EditKidsCompetiteurProfileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PostController extends AbstractController
{
    #[Route('/', name: 'app_landing')]
    public function renderLanding(): Response
    {

        return $this->render('pages/index.html.twig');
    }

    #[Route('/kongs-fighting-championship', name: 'app_kongs')]
    public function renderKongs()
    {
        return $this->render('pages/kongs.html.twig');
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
public function renderProfile(UserInterface $user, KidsRepository $kidsRepository): Response
{
    $kids = $kidsRepository->findBy(['user' => $user]);

    // if (!$kids) {
    //     throw $this->createNotFoundException('No kids found for this user');
    // }

    return $this->render('usersPages/profile.html.twig', ['kids' => $kids]);
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

    // Récupérer les enfants associés à l'utilisateur actuel
    $kids = $user->getKids();

    // Vérifier si l'utilisateur a au moins un enfant
    if ($kids->isEmpty()) {
       
    }

    // Récupérer l'ID du premier enfant (le plus simple car on suppose qu'il y a au moins un enfant)
    $kid = $kids->first();

    $form = $this->createForm(EditKidsCompetiteurProfileType::class, $user);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Pas besoin de persister l'entité car elle est déjà gérée par Doctrine
        $em->flush();

        $this->addFlash('message', 'Profil mis à jour');
        return $this->redirectToRoute('app_profile');
    }

    return $this->render('usersPages/editKidsCompetiteur.html.twig', [
        'form' => $form->createView(),
        'kids_id' => $kid->getId(), // Passer l'ID de l'enfant au template si nécessaire
    ]);
}



 
      #[Route("/edit-competiteur", name: "app_edit_competiteur_profile")]
     
      public function editCompetiteur(Request $request, Security $security, EntityManagerInterface $entityManager): Response
      {
          // Fetching the current user (assuming they are logged in)
          /** @var Users $user */
          $user = $security->getUser();
      
          // Creating the form
          $form = $this->createForm(EditCompetiteurProfileType::class, $user);
      
          // Handle the request
          $form->handleRequest($request);
      
          // Check if the form is submitted and valid
          if ($form->isSubmitted() && $form->isValid()) {
              // Save the user's data to the database
              $entityManager->persist($user);
              $entityManager->flush();
      
              // Redirect to the profile page (change this route to wherever you want)
              return $this->redirectToRoute('app_profile'); // Assuming 'app_profile' is a route name to user's profile
          }
      
          // Render the form
          return $this->render('usersPages/editcompetiteur.html.twig', [
              'form' => $form->createView(),
          ]);
      }


    #[Route('/profile/modifier-kids', name: 'app_edit_kids_profile')]
    public function renderEditKidsProfile(Request $request, EntityManagerInterface $em)
    {
        $user = $this->getUser();
    
        // Récupérer les enfants associés à l'utilisateur actuel
        $kids = $user->getKids();
    
        // Vérifier si l'utilisateur a au moins un enfant
        if ($kids->isEmpty()) {
            // Gérer le cas où l'utilisateur n'a pas d'enfant associé.
            // Vous pouvez renvoyer une erreur, une redirection, etc.
        }
    
        // Récupérer l'ID du premier enfant (le plus simple car on suppose qu'il y a au moins un enfant)
        $kid = $kids->first();
    
        $form = $this->createForm(EditKidsProfileType::class, $kid);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Pas besoin de persister l'entité car elle est déjà gérée par Doctrine
            $em->flush();
    
            $this->addFlash('message', 'Profil mis à jour');
            return $this->redirectToRoute('app_profile');
        }
    
        return $this->render('usersPages/editKidsProfile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

  
    
}