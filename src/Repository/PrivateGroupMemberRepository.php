<?php

namespace App\Repository;

use App\Entity\PrivateGroupMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PrivateGroupMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrivateGroupMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrivateGroupMember[]    findAll()
 * @method PrivateGroupMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrivateGroupMemberRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PrivateGroupMember::class);
    }

// Jointure fournissant la liste des PrivateGroup avec ses caractéristiques pour un membre
public function privateGroupForUser($member)
{
    $connexion = $this->getEntityManager()->getConnection();

    $memberId = $member->getId();
    // $reqSQL = "SELECT private_group_member.*, private_group.name, private_group.description 
    //             FROM private_group_member 
    //             INNER JOIN private_group 
    //             ON private_group_member.private_group_id = private_group.id 
    //             WHERE private_group_member.member_id = $memberId
    //         ";
    $reqSQL = "SELECT private_group_member.*, private_group.name, private_group.description, pgmj.nb_join 
                FROM private_group_member 
                INNER JOIN private_group 
                ON private_group_member.private_group_id = private_group.id 
                LEFT JOIN (SELECT private_group_id, COUNT(*) AS nb_join
                            FROM private_group_member
                            WHERE join_status = 'JOIN'
                            GROUP BY private_group_id) AS pgmj
                ON private_group_member.private_group_id = pgmj.private_group_id
                WHERE private_group_member.member_id = $memberId
                ";

    $reqDB = $this->getEntityManager()->getConnection()->prepare($reqSQL);
    $reqDB->execute();
    return $reqDB->fetchAll();
}

// Jointure fournissant la liste des PrivateGroup 
public function waitingToJoinPrivateGroup($member)
{
    $connexion = $this->getEntityManager()->getConnection();

    $memberId = $member->getId();
    $reqSQL = "SELECT private_group_member.*, private_group.name, private_group.description, pgmj.nb_join 
                FROM private_group_member 
                INNER JOIN private_group 
                ON private_group_member.private_group_id = private_group.id 
                LEFT JOIN (SELECT private_group_id, COUNT(*) AS nb_join
                            FROM private_group_member
                            WHERE join_status = 'JOIN'
                            GROUP BY private_group_id) AS pgmj
                ON private_group_member.private_group_id = pgmj.private_group_id
                WHERE private_group_member.member_id = $memberId
            ";
    
    $reqDB = $this->getEntityManager()->getConnection()->prepare($reqSQL);
    $reqDB->execute();
    return $reqDB->fetchAll();
}

// Jointure fournissant la liste des membres d'un PrivateGroup
public function userInPrivateGroup($privateGroup)
{
    $connexion = $this->getEntityManager()->getConnection();

    $privateGroupId = $privateGroup->getId();
    $reqSQL = "SELECT private_group_member.*, member.username, member.email 
                FROM private_group_member 
                INNER JOIN member 
                ON private_group_member.member_id = member.id 
                WHERE private_group_member.private_group_id = $privateGroupId
            ";
    
    $reqDB = $this->getEntityManager()->getConnection()->prepare($reqSQL);
    $reqDB->execute();
    return $reqDB->fetchAll();
}

// Jointure fournissant la liste des membres souhaitant rejoindre un PrivateGroup
public function userJoinPrivateGroup($privateGroup)
{
    $connexion = $this->getEntityManager()->getConnection();

    $privateGroupId = $privateGroup->getId();
    $reqSQL = "SELECT private_group_member.*, member.username, member.email 
                FROM private_group_member 
                INNER JOIN member 
                ON private_group_member.member_id = member.id 
                WHERE private_group_member.private_group_id = $privateGroupId AND private_group_member.join_status = 'JOIN'
            ";
    
    $reqDB = $this->getEntityManager()->getConnection()->prepare($reqSQL);
    $reqDB->execute();
    return $reqDB->fetchAll();
}

// Jointure fournissant la liste des membres n'appartenant pas à un PrivateGroup
public function userOutPrivateGroup($privateGroup)
{
    $connexion = $this->getEntityManager()->getConnection();

    $privateGroupId = $privateGroup->getId();
    $reqSQL = "SELECT member.id AS member_id, member.username, member.email 
                FROM member 
                LEFT JOIN (SELECT * FROM private_group_member WHERE private_group_member.private_group_id = $privateGroupId) AS pgm
                ON member.id = pgm.member_id 
                WHERE pgm.member_id IS null
            ";
    
    $reqDB = $this->getEntityManager()->getConnection()->prepare($reqSQL);
    $reqDB->execute();
    return $reqDB->fetchAll();
}

// Jointure fournissant la liste des PrivateGroup auxquels n'appartient pas un membre
public function privateGroupWithOutUser($user)
{
    $connexion = $this->getEntityManager()->getConnection();

    $userId = $user->getId();
    $reqSQL = "SELECT private_group.* 
                FROM private_group 
                LEFT JOIN (SELECT * FROM private_group_member WHERE private_group_member.member_id = $userId) AS pgm
                ON private_group.id = pgm.private_group_id 
                WHERE pgm.private_group_id IS null
            ";
    
    $reqDB = $this->getEntityManager()->getConnection()->prepare($reqSQL);
    $reqDB->execute();
    return $reqDB->fetchAll();
}

// Requête de suppression d'un PrivateGroup dans l'association private_group_member (entité : PrivateGroupMember)
public function privateGroupRemove($privateGroupId)
{
    $connexion = $this->getEntityManager()->getConnection();

    $reqSQL = "DELETE FROM private_group_member
                WHERE private_group_id = '$privateGroupId'
            ";
    
    $reqDB = $this->getEntityManager()->getConnection()->prepare($reqSQL);
    
    return $reqDB->execute();
}

// Requête de suppression d'un membre dans un PrivateGroup dans l'association private_group_member (entité : PrivateGroupMember)
public function privateGroupRemoveMember($privateGroupId, $memberId)
{
    $connexion = $this->getEntityManager()->getConnection();

    $reqSQL = "DELETE FROM private_group_member
                WHERE private_group_id = '$privateGroupId' AND member_id = '$memberId'
            ";
    
    $reqDB = $this->getEntityManager()->getConnection()->prepare($reqSQL);
    
    return $reqDB->execute();
}

//    /**
//     * @return PrivateGroupMember[] Returns an array of PrivateGroupMember objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PrivateGroupMember
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
