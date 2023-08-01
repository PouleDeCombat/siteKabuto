<?php

namespace App\Controller;

use app\Entity\Competiteurs;
use App\Entity\Competitions;
use App\Form\CompetitionFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CompetitionController extends AbstractController
{
    #[Route('/admin/competitions', name: 'app_competitions')]
    public function listCompetitions(CompetitionsRepository $competitionsRepository): Response
    {
        
    $competitions = $competitionsRepository->findAll();

   
    return $this->render('admin/competitions.html.twig', [
            'controller_name' => 'CompetitionController', 
            'competitions' => $competitions
        ]);
    }


    #[Route('/admin/addcompetition', name: 'app_add_competition')]
    public function addCompetition(Request $request, EntityManagerInterface $em)
    {
        $competition = new Competitions();
        
        $form = $this->createForm(CompetitionFormType::class, $competition);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
       
            $em->persist($competition);
            $em->flush();

            $this->addFlash('message', 'Competition ajouter');
            return $this->redirectToRoute('app_competitions');

        }
        return $this->render('admin/addCompetition.html.twig',[
            'form' => $form->createView(),
        ]);
    }


    #[Route('/admin/competition/{id}', name:'app_competition_delete', methods:["DELETE"])]
public function delete(Request $request, Competitions $competition, EntityManagerInterface $entityManager): Response
{
    $entityManager->remove($competition);
    $entityManager->flush();

    return $this->redirectToRoute('app_competitions');
}


    #[Route('/profil/competitions/{id}', name: 'app_inscription_competition')]
    public function listUsersCompetitions(CompetitionsRepository $competitionsRepository, int $id=null, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
       
        if ($id !== null){ 
            
            $competitions = $competitionsRepository->find($id);

            
            $competitions->addUser($user);

            $em->persist($competitions);
            $em->flush();

        }
    $competitions = $competitionsRepository->findAll();

   
    return $this->render('usersPages/mescompetitions.html.twig', [
            'controller_name' => 'CompetitionController', 
            'competitions' => $competitions
            
        ]);
    }


    #[Route('/profile/competitions', name: 'app_user_competitions')]
    public function renderUserCompetition(CompetitionsRepository $competitionsRepository): Response
    {
        $competitions=$competitionsRepository->findAll();


        return $this->render('usersPages/mescompetitions.html.twig', [
            'competitions' => $competitions
        ]);
    }



    #[Route('/admin/competitions/{id}/competiteurs', name: 'app_competition_competiteur')]
    public function getCompetitionUsers($id, CompetitionsRepository $competitionsRepository): Response
    {
        $competition = $competitionsRepository->findCompetitionWithUsers($id);

        return $this->render('admin/listcompetiteur.html.twig', [
            'competition' => $competition,
        ]);
    }




   
/**
 * @Route("/user/panel", name="user_panel")
 */
public function showUserPanel(): Response
{
    /** @var \App\Entity\Users $user */
    $user = $this->getUser();

    // récupère toutes les compétitions auxquelles l'utilisateur est inscrit
    $competitions = $user->getCompetitions();

    // renvoie la réponse
    return $this->render('usersPages/mescompetitions.html.twig', [
        'competitions' => $competitions,
    ]);
}







 #[Route('/profile/competitions/{id}', name: 'app_competition_unsubscribe')]
 public function unsubscribe(int $id, EntityManagerInterface $em, CompetitionsRepository $competitionRepository)
 {
     $user = $this->getUser();

    if (!$user instanceof \App\Entity\Users) {
        throw new \LogicException('The User object must be an instance of App\Entity\Users');
    }

     $competition = $competitionRepository->find($id);

     

    if ($competition && $user->getCompetitions()->contains($competition)) {
        
        $user->removeCompetition($competition);
        $competition->removeUser($user); 
        $em->flush();
    }

    $competitions = $user->getCompetitions();

    // renvoie la réponse
    return $this->render('usersPages/mescompetitions.html.twig', [
        'competitions' => $competitions,
    ]);
 }











}
