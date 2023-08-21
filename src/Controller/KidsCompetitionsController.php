<?php

namespace App\Controller;

use App\Entity\Kids;
use App\Entity\KidsCompetitions;
use App\Repository\KidsRepository;
use App\Form\KidsCompetitionFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use App\Form\EditKidsCompetiteurProfileType;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\KidsCompetitionsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class KidsCompetitionsController extends AbstractController
{
    #[Route('/admin/kids-competitions', name: 'app_kids_competitions')]
    public function ListKidsCompetitions(KidsCompetitionsRepository $kidsCompetitionsRepository): Response
    {
        $kidsCompetitions = $kidsCompetitionsRepository->findAll();

        return $this->render('admin/kidsCompetitions.html.twig', [
            'controller_name' => 'KidsCompetitionsController',
            'kidsCompetitions' => $kidsCompetitions
        ]);
    }

 


    #[Route('/admin/add-kids-competition', name: 'app_add_kids_competition')]
    public function addCompetition(Request $request, EntityManagerInterface $em)
    {
        $kidscompetition = new KidsCompetitions();
        
        $form = $this->createForm(KidsCompetitionFormType::class, $kidscompetition);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
       
            $em->persist($kidscompetition);
            $em->flush();

            $this->addFlash('message', 'Competition ajouter');
            return $this->redirectToRoute('app_kids_competitions');

        }
        return $this->render('admin/addKidsCompetition.html.twig',[
            'form' => $form->createView(),
        ]);
    }


    #[Route('/admin/competition/{id}', name:'app_competition_delete', methods:["DELETE"])]
    public function delete(Request $request, KidsCompetitions $kidsCompetition, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($kidsCompetition);
        $entityManager->flush();
    
        return $this->redirectToRoute('app_kids_competitions');
    }


//     #[Route('/profile/kids-competiteur', name: 'app_profile_kids_competiteur')]
// public function renderEditProfileKidsCompetiteur(Request $request, EntityManagerInterface $em)
// {
//     $user = $this->getUser();
    
//     // Récupérer les enfants associés à l'utilisateur actuel
//     $kidsRepository = $em->getRepository(Kids::class);
//     $kids = $kidsRepository->findBy(['user' => $user]);

//     // Vous pouvez utiliser le premier enfant s'il existe, ou gérer le cas où il y a plusieurs enfants associés à l'utilisateur.

//     if (count($kids) > 0) {
//         $kid = $kids[0]; // Prendre le premier enfant associé à l'utilisateur
//         $form = $this->createForm(EditKidsCompetiteurProfileType::class, $kid);

//         $form->handleRequest($request);

//         if ($form->isSubmitted() && $form->isValid()) {
//             // Pas besoin de persister l'entité car elle est déjà gérée par Doctrine
//             $em->flush();

//             $this->addFlash('message', 'Profil enfant mis à jour');
//             return $this->redirectToRoute('app_profile');
//         }

//         return $this->render('usersPages/editKidsCompetiteur.html.twig', [
//             'form' => $form->createView(),
//         ]);
//     } else {
//         // Gérer le cas où aucun enfant n'est associé à l'utilisateur
//         throw $this->createNotFoundException('Aucun enfant trouvé pour cet utilisateur.');
//     }
// }


#[Route("/profile/kids-competiteur/{id}", name: "app_profile_kids_competiteur", requirements: ["id" => "\d+"])]
    public function renderEditProfileKidsCompetiteur($id, Request $request, EntityManagerInterface $em)
{
    // Récupérer l'enfant spécifique basé sur l'ID
    $kidRepository = $em->getRepository(Kids::class);
    $kid = $kidRepository->find($id);

    if (!$kid || $kid->getUser() !== $this->getUser()) {
        // Gérer le cas où aucun enfant n'est associé à l'utilisateur ou l'enfant ne correspond pas à l'utilisateur actuel
        throw $this->createNotFoundException('Aucun enfant trouvé pour cet utilisateur ou accès non autorisé.');
    }

    $form = $this->createForm(EditKidsCompetiteurProfileType::class, $kid);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('message', 'Profil enfant mis à jour');
        return $this->redirectToRoute('app_profile');
    }

    return $this->render('usersPages/editKidsCompetiteur.html.twig', [
        'form' => $form->createView(),
    ]);
}







#[Route('/profile/competitions-kids', name: 'app_list_kids_competitions')]
public function listKidsCompetition(KidsCompetitionsRepository $kidsCompetitionsRepository): Response
{
    /** @var \App\Entity\Users $user */
    $user = $this->getUser();
    $kids = $user->getKids(); // Assuming you have multiple kids associated with the user.

    $kidsCompetitions = $kidsCompetitionsRepository->findAll();
    $availableCompetitions = $kidsCompetitions; // assume all competitions are available for this user

    return $this->render('usersPages/mescompetitionsKids.html.twig', [
        'KidsCompetitions' => $kidsCompetitions,
        'availableCompetitions' => $availableCompetitions,
        'kids' => $kids, // Pass the $kids variable to the view
    ]);
}





#[Route('/profile/inscription-competition/{kid_id}/{competition_id}', name: 'app_inscription_kids_competition')]
public function registerKidForCompetition(
    int $kid_id,
    int $competition_id,
    KidsRepository $kidsRepository,
    KidsCompetitionsRepository $kidsCompetitionsRepository,
    EntityManagerInterface $em
): Response {
    $user = $this->getUser();

    // Find the Kid entity with the given $kid_id
    $kid = $kidsRepository->find($kid_id);
    if (!$kid || $kid->getUser() !== $user) {
        throw $this->createNotFoundException('Kid not found or does not belong to the current user.');
    }

    // Find the KidsCompetition entity with the given $competition_id
    $competition = $kidsCompetitionsRepository->find($competition_id);
    if (!$competition) {
        throw $this->createNotFoundException('Competition not found.');
    }

    // Add the kid to the competition and persist the changes
    $competition->addKid($kid);
    $em->persist($competition);
    $em->flush();

    $this->addFlash('success', 'Inscription réussie.');

    return $this->redirectToRoute('app_list_kids_competitions');
}



#[Route('/admin/competitions-kids/{id}/competiteurs-kids', name: 'app_competition_kids_competiteur')]
public function getCompetitionUsers(int $id, KidsCompetitionsRepository $kidsCompetitionsRepository): Response
{
    // Find the competition with the given $id
    $kidsCompetition = $kidsCompetitionsRepository->find($id);

    // If the competition doesn't exist, throw an exception
    if (!$kidsCompetition) {
        throw $this->createNotFoundException('Competition not found.');
    }

    // Get the list of kids associated with the competition
    $kids = $kidsCompetition->getKids();

    return $this->render('admin/listkidscompetiteur.html.twig', [
        'kidsCompetition' => $kidsCompetition,
        'kids' => $kids,
    ]);
}



#[Route('/profile/kids-competitions', name: 'app_kids_competitions_for_user')]
public function displayKidsCompetitionsForUser(KidsRepository $kidsRepository, KidsCompetitionsRepository $kidsCompetitionsRepository): Response
{
    $kids = $kidsRepository->findBy(['user' => $this->getUser()]);
    $competitions = $kidsCompetitionsRepository->findAll();

    return $this->render('path_to_your_template.html.twig', [
        'kids' => $kids,
        'competitions' => $competitions,
    ]);
}

#[Route('/register-kid-to-competition/{competitionId}/{kidId}', name: 'register_kid_to_competition')]
public function registerKidToCompetition(int $competitionId, int $kidId, EntityManagerInterface $em)
{
    $competition = $em->getRepository(KidsCompetitions::class)->find($competitionId);
    $kid = $em->getRepository(Kids::class)->find($kidId);

    if (!$competition || !$kid) {
        throw $this->createNotFoundException('Competition ou enfant non trouvé');
    }

    $kid->addKidsCompetition($competition); // Assurez-vous que cette méthode existe dans votre entité Kids.
    $em->flush();

    $this->addFlash('success', 'Enfant inscrit à la compétition avec succès');
    return $this->redirectToRoute('app_kids_competitions_for_user');
}





      #[Route('/desinscription/kid/{kid_id}/competition/{competition_id}', name: 'app_desinscription_kids_competition')]
     
    public function unregisterKidFromCompetition($kid_id, $competition_id, EntityManagerInterface $em): Response
    {
        $kid = $em->getRepository(Kids::class)->find($kid_id);
        $competition = $em->getRepository(KidsCompetitions::class)->find($competition_id);

        if (!$kid || !$competition) {
            $this->addFlash('error', 'Enfant ou compétition non trouvée.');
            return $this->redirectToRoute('app_list_kids_competitions');
        }

        // Suppose you have a many-to-many relation between Kid and Competition
        $kid->removeKidsCompetition($competition);
        
        $em->persist($kid);
        $em->flush();

        $this->addFlash('success', 'L\'enfant a été désinscrit de la compétition avec succès.');
        
        return $this->redirectToRoute('app_list_kids_competitions');
    }





}
