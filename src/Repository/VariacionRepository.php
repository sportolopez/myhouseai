<?php

namespace App\Repository;

use App\Entity\Variacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Variacion>
 *
 * @method Variacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Variacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Variacion[]    findAll()
 * @method Variacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VariacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Variacion::class);
    }

//    /**
//     * @return Variacion[] Returns an array of Variacion objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }
public function findByImagen($value): ?array
{
    return $this->createQueryBuilder('v')
        ->andWhere('v.imagen = :val')
        ->setParameter('val', $value)
        ->getQuery()
        ->getResult()
    ;
}
    public function findByImagenSinBlob($value): ?array
    {
        return $this->createQueryBuilder('v')
            ->select('v.id, v.fecha, v.roomType, v.style')
            ->andWhere('v.imagen = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }
}
