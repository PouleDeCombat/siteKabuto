<?php

namespace App\Repository;

use App\Entity\Cours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cours>
 *
 * @method Cours|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cours|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cours[]    findAll()
 * @method Cours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cours::class);
    }

    public function save(Cours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Cours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }



    public function getCoursesForSeason(): array {
        $allEvents = [];
    
        // Dates de début et de fin de la saison
        $startDate = new \DateTime('2023-09-04');
        $endDate = new \DateTime('2024-06-30');
    
        // Calculer le nombre de semaines entre les deux dates
        $interval = $startDate->diff($endDate);
        $numberOfWeeks = intdiv($interval->days, 7);
    
        $courses = $this->entityManager->getRepository(Cours::class)->findAll();
    
        foreach ($courses as $course) {
            for ($week = 0; $week < $numberOfWeeks; $week++) {
                $start = clone $course->getStart();
                $end = clone $course->getEnd();
    
                $event = [
                    'id' => $course->getId(),
                    'title' => $course->getDiscipline(),
                    'start' => $start->modify("+$week weeks")->format('Y-m-d H:i:s'),
                    'end' => $end->modify("+$week weeks")->format('Y-m-d H:i:s'),
                    'extendedProps' => [
                        'discipline' => $course->getDiscipline(),
                        'niveau' => $course->getNiveau(),
                        // Ajoutez d'autres propriétés si nécessaire
                    ],
                    'backgroundColor' => $course->getBackgroundColor(),
                ];
    
                $allEvents[] = $event;
            }
        }
    
        return $allEvents;
    }
    

//    /**
//     * @return Cours[] Returns an array of Cours objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Cours
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
