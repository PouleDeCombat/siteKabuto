<?php

namespace App\Controller;

use App\Repository\CoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_calendar')]
    public function index(CoursRepository $cours): Response
    {
        $events = $cours->findAll();

        $leçon = [];

        foreach($events as $event){
            $leçon[] = [
                'id' => $event->getId(),
                'start' => $event->getStart()->format('Y-m-d H:i:s'),
                'end' => $event->getEnd()->format('Y-m-d H:i:s'),
                'discipline' => $event->getDiscipline(),
                'niveau' => $event->getNiveau(),
                'backgroundColor' => $event->getBackgroundColor(),
                     'rrule' => 'FREQ=WEEKLY;BYDAY=WE',
                
            ];
        }
            $data = json_encode($leçon);
            return $this->render('calendar/index.html.twig', compact('data'));
    }

    // public function index(CoursRepository $coursRepository): Response
    // {
    //     $coursList = $coursRepository->getCoursesForSeason();  // Hypothèse: cela renvoie une liste de cours
    
    //     $events = [];
    //     foreach ($coursList as $cours) {
    //         $events[] = [
    //             'id' => $cours->getId(),
    //             'start' => $cours->getStartDate()->format('Y-m-d H:i:s'),
    //             'end' => $cours->getEndDate()->format('Y-m-d H:i:s'),
    //             'rrule' => 'FREQ=WEEKLY;BYDAY=WE',  // Note: Ici, "WE" représente "Mercredi". Vous devrez ajuster cela en fonction du jour de la semaine de chaque cours.
    //             // ... ajoutez d'autres propriétés si nécessaire
    //         ];
    //     }
        
    //     $data = json_encode($events);
    //     return $this->render('calendar/index.html.twig', compact('data'));
    // }
    

}
