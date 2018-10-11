<?php

namespace App\Repository;

use App\Entity\Insta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Insta|null find($id, $lockMode = null, $lockVersion = null)
 * @method Insta|null findOneBy(array $criteria, array $orderBy = null)
 * @method Insta[]    findAll()
 * @method Insta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Insta::class);
    }

//    /**
//     * @return Insta[] Returns an array of Insta objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Insta
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function research($word)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT i
                  FROM App\Entity\Insta i
                  WHERE i.tags
                  LIKE :val
                  ORDER BY i.created_time DESC'
        )->setParameter('val', $word);

        // returns an array of Product objects
        return $query->execute();

    }
}
