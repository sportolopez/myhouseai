<?php

namespace App\Repository;

use App\Entity\Imagen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Imagen>
 *
 * @method Imagen|null find($id, $lockMode = null, $lockVersion = null)
 * @method Imagen|null findOneBy(array $criteria, array $orderBy = null)
 * @method Imagen[]    findAll()
 * @method Imagen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImagenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Imagen::class);
    }

//    /**
//     * @return Imagen[] Returns an array of Imagen objects
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
        public function findByUsuarioEmail(string $email): array
        {
            return $this->createQueryBuilder('i')
            ->innerJoin('i.Usuario', 'u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult();
        }
        public function findByUsuarioId(int $usuarioId): array
        {
            return $this->createQueryBuilder('i')
            ->andWhere('i.Usuario = :usuarioId')
            ->setParameter('usuarioId', $usuarioId)
            ->getQuery()
            ->getResult();
        }
}
