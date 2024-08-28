<?php

namespace App\Repository;

use App\Entity\Imagen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Query;
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
            $query = $this->createQueryBuilder('i')
            ->select('i.id, i.fecha, i.estilo, i.tipoHabitacion')
            ->innerJoin('i.Usuario', 'u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery();
        
            $resultados = $query->getArrayResult();
            
            $imagenes = [];
            
            foreach ($resultados as $resultado) {
                $imagen = new Imagen();
                $imagen->setId($resultado['id']);
                $imagen->setFecha($resultado['fecha']);
                $imagen->setEstilo($resultado['estilo']);
                $imagen->setTipoHabitacion($resultado['tipoHabitacion']);
                
                // No establezcas imgOrigen, o establece un valor por defecto
                $imagenes[] = $imagen;
            }
            return $imagenes;
        }
        public function findByUsuarioId(int $usuarioId): array
        {
            $query = $this->createQueryBuilder('i')
            ->select('i.id, i.fecha, i.estilo, i.tipoHabitacion') // Especifica aquí los campos que necesitas
            ->andWhere('i.Usuario = :usuarioId')
            ->setParameter('usuarioId', $usuarioId)
            ->getQuery()
            ->getResult();

            $resultados = $query->getArrayResult();
            
            $imagenes = [];
            
            foreach ($resultados as $resultado) {
                $imagen = new Imagen();
                $imagen->setId($resultado['id']);
                $imagen->setFecha($resultado['fecha']);
                $imagen->setEstilo($resultado['estilo']);
                $imagen->setTipoHabitacion($resultado['tipoHabitacion']);
                
                // No establezcas imgOrigen, o establece un valor por defecto
                $imagenes[] = $imagen;
            }
            return $imagenes;
        }

        public function findOneById(String $id): ?Imagen
        {
            $query = $this->createQueryBuilder('i')
                ->select('i.id, i.fecha, i.estilo, i.tipoHabitacion, i.renderId') // Especifica los campos que necesitas
                ->where('i.id = :id')
                ->setParameter('id', $id)
                ->getQuery();
    
            $result = $query->getOneOrNullResult();
    
            if ($result === null) {
                throw new \Exception("No se encontro la imagen $id");
            }
    
            // Crear una instancia de Imagen y asignar manualmente los valores
            $imagen = new Imagen();
            $imagen->setId($result['id']);
            $imagen->setFecha($result['fecha']);
            $imagen->setEstilo($result['estilo']);
            $imagen->setTipoHabitacion($result['tipoHabitacion']);
            $imagen->setRenderId($result['renderId']);
    
            return $imagen;
        }
}
