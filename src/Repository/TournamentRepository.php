<?php

namespace App\Repository;

use App\Entity\Tournament;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Tournament|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tournament|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tournament[]    findAll()
 * @method Tournament[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Tournament::class);
    }

    // /**
    //  * @return Tournament[] Returns an array of Tournament objects
    //  */
    
    public function findByAdmin($value)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.admins', 'a')
            ->andWhere('a.id = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    

    /*
    public function findOneBySomeField($value): ?Tournament
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
