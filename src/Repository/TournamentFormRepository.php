<?php

namespace App\Repository;

use App\Entity\TournamentForm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TournamentForm|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentForm|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentForm[]    findAll()
 * @method TournamentForm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentFormRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TournamentForm::class);
    }

    // /**
    //  * @return TournamentForm[] Returns an array of TournamentForm objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    
    public function findOneByUri($value): ?TournamentForm
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.uri = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
