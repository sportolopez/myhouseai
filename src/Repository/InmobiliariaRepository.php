<?php

namespace App\Repository;

use App\Entity\Inmobiliaria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inmobiliaria>
 *
 * @method Inmobiliaria|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inmobiliaria|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inmobiliaria[]    findAll()
 * @method Inmobiliaria[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InmobiliariaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inmobiliaria::class);
    }

//    /**
//     * @return Inmobiliaria[] Returns an array of Inmobiliaria objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Inmobiliaria
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

public function findAllOrderedByImagenEjemplo(): array
{
    return $this->createQueryBuilder('i')
        ->orderBy('i.imagen_ejemplo', 'DESC') // Usa otro criterio si `imagenEjemplo` no es adecuado para ordenamiento
        ->getQuery()
        ->getResult();
}
}
