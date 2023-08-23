<?php

namespace App\Controller;

use DateTime;
use App\Entity\Cours;
use App\Form\CoursType;
use App\Entity\Reservations;
use App\Repository\CoursRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationsRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/cours')]
class CoursController extends AbstractController
{
    #[Route('/', name: 'app_cours_index', methods: ['GET'])]
    public function index(CoursRepository $coursRepository): Response
    {
        return $this->render('cours/index.html.twig', [
            'cours' => $coursRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_cours_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CoursRepository $coursRepository): Response
    {
        $cour = new Cours();
        $form = $this->createForm(CoursType::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coursRepository->save($cour, true);

            return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cours/new.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cours_show', methods: ['GET'])]
    public function show(Cours $cour): Response
    {
        return $this->render('cours/show.html.twig', [
            'cour' => $cour,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cours_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cours $cour, CoursRepository $coursRepository): Response
    {
        $form = $this->createForm(CoursType::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coursRepository->save($cour, true);

            return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cours/edit.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cours_delete', methods: ['POST'])]
    public function delete(Request $request, Cours $cour, CoursRepository $coursRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cour->getId(), $request->request->get('_token'))) {
            $coursRepository->remove($cour, true);
        }

        return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
    }


    
    #[Route('/coursDetail/{id}', name: 'cours_detail', requirements: ['id' => '\d+'])]
    public function showDetail(
        int $id, 
        CoursRepository $coursRepository, 
        ReservationsRepository $reservationsRepo
    ): Response {
        // Récupération du cours via le dépôt injecté
        $cours = $coursRepository->find($id);
    
        // Si le cours n'existe pas, retournez une erreur 404
        if (!$cours) {
            throw $this->createNotFoundException('Le cours demandé n\'existe pas.');
        }
    
        // Vérifiez si l'utilisateur est déjà inscrit à ce cours
        $existingReservation = $reservationsRepo->findByUserAndCours($this->getUser()->getId(), $cours->getId());
    
        // Sinon, rendez la vue avec les détails du cours
        return $this->render('cours/coursDetail.html.twig', [
            'cours' => $cours,
            'existingReservation' => $existingReservation
        ]);
    }



#[Route("/reservation/create", name: "reservation_create", methods: ["POST"])]
public function create(Request $request, EntityManagerInterface $em, UsersRepository $userRepo, CoursRepository $coursRepo): Response
{
    $userId = $this->getUser()->getId(); // Assurez-vous que l'utilisateur est connecté
    $coursId = $request->request->get('cours_id');

    $user = $userRepo->find($userId);
    $cours = $coursRepo->find($coursId);

    if (!$user || !$cours) {
        // Gérez l'erreur comme vous le souhaitez (ex : message d'erreur, redirection, etc.)
        return $this->redirectToRoute('home');
    }

    $reservation = new Reservations();
    $reservation->addUser($user); // Ici, nous utilisons addUser au lieu de setUser
    $reservation->setCours($cours);
    
    
    $em->persist($reservation);
    $em->flush();

    $this->addFlash('success', 'Inscription réussie !');

    return $this->redirectToRoute('app_calendar'); // Ou redirigez où vous le souhaitez
}




#[Route("/reservation/delete", name: "reservation_delete", methods: ["POST"])]
public function deleteRservation(
    Request $request,
    EntityManagerInterface $em,
    ReservationsRepository $reservationsRepo
): Response {
    $userId = $this->getUser()->getId();  // Ensure the user is logged in
    $coursId = $request->request->get('cours_id');

    // Find the reservation for the given user and cours_id
    $reservation = $reservationsRepo->findByUserAndCours($userId, $coursId);

    if ($reservation) {
        $em->remove($reservation);
        $em->flush();

        $this->addFlash('success', 'Vous avez été désinscrit du cours avec succès !');
    } else {
        $this->addFlash('error', 'Erreur lors de la désinscription. Veuillez réessayer.');
    }

    return $this->redirectToRoute('app_calendar');  // Or redirect wherever you wish
}





// #[Route("/reservation/create", name: "reservation_create", methods: ["POST"])]
// public function create(Request $request, EntityManagerInterface $em, UsersRepository $userRepo, CoursRepository $coursRepo): Response
// {
//     $userId = $this->getUser()->getId();
//     $coursId = $request->request->get('cours_id');

//     $user = $userRepo->find($userId);
//     $cours = $coursRepo->find($coursId);

//     // Vérifiez si l'utilisateur est déjà inscrit à ce cours
//     $existingReservation = $em->getRepository(Reservations::class)->findOneBy([
//         'user' => $user,
//         'cours' => $cours
//     ]);

//     if ($existingReservation) {
//         $this->addFlash('warning', 'Vous êtes déjà inscrit à ce cours !');
//         return $this->redirectToRoute('app_calendar');
//     }

//     $reservation = new Reservations();
//     $reservation->addUser($user);
//     $reservation->setCours($cours);

//     $em->persist($reservation);
//     $em->flush();

//     $this->addFlash('success', 'Inscription réussie !');

//     return $this->redirectToRoute('app_calendar');
// }

// #[Route("/reservation/delete/{id}", name: "reservation_delete", methods: ["POST"])]
// public function deleteReservation(int $id, EntityManagerInterface $em): Response
// {
//     $reservation = $em->getRepository(Reservations::class)->find($id);
//     if (!$reservation) {
//         $this->addFlash('error', 'Réservation introuvable.');
//         return $this->redirectToRoute('app_calendar');
//     }

//     $em->remove($reservation);
//     $em->flush();

//     $this->addFlash('success', 'Désinscription réussie !');

//     return $this->redirectToRoute('app_calendar');
// }





  



}
