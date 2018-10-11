<?php

namespace App\Repository;

use App\Entity\CloudMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CloudMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method CloudMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method CloudMessage[]    findAll()
 * @method CloudMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CloudMessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CloudMessage::class);
    }

//    /**
//     * @return CloudMessage[] Returns an array of CloudMessage objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CloudMessage
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
