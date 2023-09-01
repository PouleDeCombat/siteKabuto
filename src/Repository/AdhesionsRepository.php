<?php

namespace App\Repository;

use App\Entity\Users;
use App\Entity\Adhesions;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Adhesions>
 *
 * @method Adhesions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Adhesions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Adhesions[]    findAll()
 * @method Adhesions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdhesionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adhesions::class);
    }

    public function save(Adhesions $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Adhesions $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    
    public function findActiveAdhesionsForUser(Users $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->andWhere('a.kids IS NULL')
            ->andWhere('a.date_debut <= :now')
            ->andWhere('a.date_fin >= :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }
    

//    /**
//     * @return Adhesions[] Returns an array of Adhesions objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Adhesions
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
