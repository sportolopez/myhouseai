<?php

namespace App\Repository;

use App\Entity\EmailEnviado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailEnviado>
 *
 * @method EmailEnviado|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailEnviado|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailEnviado[]    findAll()
 * @method EmailEnviado[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailEnviadoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailEnviado::class);
    }

//    /**
//     * @return EmailEnviado[] Returns an array of EmailEnviado objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EmailEnviado
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
