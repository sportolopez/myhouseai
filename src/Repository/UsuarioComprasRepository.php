<?php

namespace App\Repository;

use App\Entity\UsuarioCompras;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UsuarioCompras>
 *
 * @method UsuarioCompras|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsuarioCompras|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsuarioCompras[]    findAll()
 * @method UsuarioCompras[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioComprasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsuarioCompras::class);
    }

//    /**
//     * @return UsuarioCompras[] Returns an array of UsuarioCompras objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UsuarioCompras
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
