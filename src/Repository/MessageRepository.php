<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Message::class);
    }

// Requête tousMessages
    public function tousMessages($infoId, $limit, $offset)
    {
        $connexion = $this->getEntityManager()->getConnection();

        $reqSQL = "SELECT *
                    FROM message
                    INNER JOIN member
                    ON message.author_id = member.id
                    WHERE message.info_id = $infoId
                    ORDER BY message.publication_date DESC
                ";
        if ($limit != 0){$reqSQL .= "LIMIT $limit OFFSET $offset";}
        
        $reqDB = $connexion->prepare($reqSQL);
        $reqDB->execute();

        return $reqDB->fetchAll();
    }


//    /**
//     * @return Message[] Returns an array of Message objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function Search($queryString)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT *
        FROM member 
        LEFT JOIN message 
        ON member.id = message.author_id
        WHERE content LIKE :val
        ORDER BY publication_date DESC
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['val' => $queryString]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }
}
