<?php

namespace App\Repository;

use App\Entity\Info;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Info|null find($id, $lockMode = null, $lockVersion = null)
 * @method Info|null findOneBy(array $criteria, array $orderBy = null)
 * @method Info[]    findAll()
 * @method Info[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Info::class);
    }

// Requête dernières infos
    public function dernieresInfos($limit, $offset)
    {
        $connexion = $this->getEntityManager()->getConnection();

        $reqSQL = "SELECT i.*, media.url AS mediaUrl, media.type AS mediaType, media.title AS mediaTitle
                    FROM (
                        SELECT info.id AS infoId, info.publication_date AS infoPublicationDate, info.title AS infoTitle, info.member_id AS memberId, member.username AS memberUsername
                        FROM info
                        INNER JOIN member
                        ON info.member_id = member.id
                        WHERE info.end_date >= NOW()
                        ORDER BY info.publication_date DESC
                        LIMIT " . ($limit+1) . "
                        OFFSET $offset
                        ) AS i
                    LEFT JOIN info_media
                    ON i.infoId = info_media.info_id
                    LEFT JOIN media
                    ON info_media.media_id = media.id
                ";
        
        $reqDB = $this->getEntityManager()->getConnection()->prepare($reqSQL);
        $reqDB->execute();

        $resultatRequete = $reqDB->fetchAll();

        $idInfo = 0;
        $indice = 0;
        $tabInfo = [];
        foreach ($resultatRequete as $info) {
            if ($info["infoId"] != $idInfo){
                $idInfo = $info["infoId"];
                $indice++;
                $tabInfo[$indice] = $info;
                $tabInfo[$indice]["media"] = [];
            }
            if ($info["mediaUrl"] != NULL){
                $tabInfo[$indice]["media"][] = ["url" => $info["mediaUrl"], "type" => $info["mediaType"], "title" => $info["mediaTitle"]];
            }
        }

        return $tabInfo;
    }

// Requête Infos filtrées par catégories
    public function infoFiltre($urlCategorie)
    {
        $connexion = $this->getEntityManager()->getConnection();

        $reqSQL = "SELECT info_filtre.*, 
                        media.id AS media_id,
                        media.title AS media_title, 
                        media.description AS media_description, 
                        media.url AS media_url, 
                        media.type AS media_type 
                    FROM (
                        SELECT info.*, member.username 
                            FROM info 
                            INNER JOIN member
                            ON info.member_id = member.id
                            WHERE info.id IN (
                                SELECT info_category.info_id 
                                    FROM info_category 
                                    WHERE info_category.category_id IN (
                                        SELECT id 
                                            FROM category 
                                            WHERE url_name = '$urlCategorie'
                    ))) AS info_filtre
                    LEFT JOIN info_media
                    ON info_filtre.id = info_media.info_id
                    LEFT JOIN media
                    ON media_id = media.id
                ";
        
        $reqDB = $connexion->prepare($reqSQL);
        $reqDB->execute();

        $resultatRequete = $reqDB->fetchAll();

        $idInfo = 0;
        $indice = 0;
        $tabInfo = [];
        foreach ($resultatRequete as $info) {
            if ($info["id"] != $idInfo){
                $idInfo = $info["id"];
                $indice++;
                $tabInfo[$indice] = $info;
                $tabInfo[$indice]["publicationDate"] = $info["publication_date"];
                $tabInfo[$indice]["endDate"] = $info["end_date"];
                $tabInfo[$indice]["member"] = ["id" => $info["member_id"], "username" => $info["username"]];
                unset($tabInfo[$indice]["publication_date"]);
                unset($tabInfo[$indice]["end_date"]);
                unset($tabInfo[$indice]["member_id"]);
                unset($tabInfo[$indice]["username"]);
                $tabInfo[$indice]["media"] = [];
            }
            if ($info["media_url"] != NULL){
                $tabInfo[$indice]["media"][] = ["id" => $info["media_id"],
                                                "url" => $info["media_url"],
                                                "type" => $info["media_type"],
                                                "title" => $info["media_title"],
                                                "description" => $info["media_description"],
                                                ];
            }
        }

        return $tabInfo;
    }


//    /**
//     * @return Info[] Returns an array of Info objects
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
    public function findOneBySomeField($value): ?Info
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
                  FROM App\Entity\Info i
                  JOIN App\Entity\Message m
                  WHERE i.description
                  LIKE :val
                  AND m.content
                  like :val'
        )->setParameter('val', $word);

        // returns an array of Product objects
        return $query->execute();

    }

    public function multiResearch($dateStart,$dateEnd)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT i
                 FROM App\Entity\Info i
                 WHERE i.publicationDate 
                 BETWEEN :dateStart AND :dateEnd'
        )->setParameter('dateStart', $dateStart)
         ->setParameter('dateEnd', $dateEnd);

        // returns an array of Product objects
        return $query->execute();
    }
}
