<?php

namespace App\Repository;

use App\Entity\KidsCompetitions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<KidsCompetitions>
 *
 * @method KidsCompetitions|null find($id, $lockMode = null, $lockVersion = null)
 * @method KidsCompetitions|null findOneBy(array $criteria, array $orderBy = null)
 * @method KidsCompetitions[]    findAll()
 * @method KidsCompetitions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KidsCompetitionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KidsCompetitions::class);
    }

    public function save(KidsCompetitions $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(KidsCompetitions $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return KidsCompetitions[] Returns an array of KidsCompetitions objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('k')
//            ->andWhere('k.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('k.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?KidsCompetitions
//    {
//        return $this->createQueryBuilder('k')
//            ->andWhere('k.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
