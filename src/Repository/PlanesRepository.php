<?php

namespace App\Repository;

use App\Entity\Planes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Planes>
 *
 * @method Planes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Planes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Planes[]    findAll()
 * @method Planes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlanesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planes::class);
    }

//    /**
//     * @return Planes[] Returns an array of Planes objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    public function findOneByMonto($value): ?Planes
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.valor = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}
