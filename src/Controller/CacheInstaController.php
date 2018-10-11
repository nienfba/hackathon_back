<?php

namespace App\Controller;

use App\Entity\Insta;
use App\Entity\Info;
use App\Entity\Member;
use App\Repository\InstaRepository;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class CacheInstaController extends AbstractController {

    /**  
     * @Route("/insta/cache", name="cache_insta")
     */
    public function search(Request $request, InstaRepository $instaRepository) {

        // https://www.instagram.com/developer/endpoints/media/
        // https://api.instagram.com/v1/locations/search?lat=43.3&lng=5.4&access_token=8439782101.da06fb6.51b1b6f220384c2c8a7e7318cc33ab65
        /*
          zonage en 10x10
          (44 - 42.8) / 10    = 0.12
          (7 - 4) / 10        = 0.3

         */
        // création d'une grille sur la map afin de récuperer via les coordonnées
        // gps les publications insta

        $tabLong = [4,4.3,4.6,4.9,5.2,5.5,5.8,6.1,6.4,6.7,7];
        $tabLat = [42.8,42.92,43.04,43.16,43.28,43.4,43.52,43.64,43.76,43.88,44];

        // coordonnées gps générées aléatoirement
        $randTabLat = mt_rand(0, 10);
        $randTabLng = mt_rand(0, 10);

        // on va les récupérer dans les tableaux
        $randLat=$tabLat[$randTabLat];
        $randLng=$tabLong[$randTabLng];

        $lat = floatval($request->get("lat", $randLat));
        $lng = floatval($request->get("lng", $randLng));

        // $distance   = $request->get("distance", 5000);
        $distance = 5000;     // ON FORCE LA DISTANCE MAX POUR OBTENIR UNE COUVERTURE MAXIMALE

        $latRef = intval($lat * 10) * 0.1;
        $lngRef = intval($lng * 20) * 0.05;
        
        $objInsta = $instaRepository->findOneBy(["insta_type" => "API", "latitude" => $latRef, "longitude" => $lngRef]);
        $em = $this->getDoctrine()->getManager();

            $urlApi = "https://api.instagram.com/v1/media/search?lat=$latRef&lng=$lngRef&distance=$distance&access_token=8439782101.da06fb6.51b1b6f220384c2c8a7e7318cc33ab65";
            $txtJsonResponse = file_get_contents($urlApi);

            if ($txtJsonResponse) {

                // EXTRACT ALL POINTS IN JSON RESPONSE                
                $tabJson = json_decode($txtJsonResponse, true);
                $tabData = $tabJson["data"] ?? [];
                foreach ($tabData as $index => $tabPost) {
                    $postId = $tabPost["id"] ?? 0;

                    $postLatitude = $tabPost["location"]["latitude"] ?? 43.3;
                    $postLongitude = $tabPost["location"]["longitude"] ?? 5.4;
                    $postLink = $tabPost["link"] ?? "";
                    $postUsername = $tabPost["user"]["username"] ?? "";
                    $postCreatedTime = $tabPost["created_time"] ?? "";
                    $postImageThumbnail = $tabPost["images"]["thumbnail"]["url"] ?? "";
                    $postImageLR = $tabPost["images"]["low_resolution"]["url"] ?? "";
                    $postImageSR = $tabPost["images"]["standard_resolution"]["url"] ?? "";
                    $postCaption = $tabPost["caption"]["text"] ?? "";
                    $postTags = implode(",",$tabPost['tags']) ?? "";
                    $postLikes = $tabPost['likes']['count'] ?? 0;

                    $objInstaPost = $instaRepository->findOneBy(["insta_id" => $postId]);
                    if ($objInstaPost == null) {
                        // NEW INSTA
                        $objInstaPost = new Insta;

                        $objInstaPost->setInstaType("insta");
                        $objInstaPost->setInstaId($postId);
                        $objInstaPost->setLatitude($postLatitude);
                        $objInstaPost->setLongitude($postLongitude);
                        $objInstaPost->setLink($postLink);
                        $objInstaPost->setUserUsername($postUsername);
                        $objInstaPost->setCreatedTime($postCreatedTime);
                        $objInstaPost->setThumbnail($postImageThumbnail);
                        $objInstaPost->setLowResolution($postImageLR);
                        $objInstaPost->setStandardResolution($postImageSR);
                        $objInstaPost->setCaption($postCaption);
                        $objInstaPost->setTags($postTags);
                        $objInstaPost->setLikes($postLikes);

                        $em->persist($objInstaPost);
                    } else {
                        // UPDATE DATA
                        
                        $objInstaPost->setLikes($postLikes);
                        $objInstaPost->setTags($postTags);
                        $objInstaPost->setLatitude($postLatitude);
                        $objInstaPost->setLongitude($postLongitude);
                        $objInstaPost->setLink($postLink);
                        $objInstaPost->setCaption($postCaption);
                        
                        $em->persist($objInstaPost);
                        
                    }
                }

                $em->flush();
            }
        
        return $this->render('cache_insta/index.html.twig', []);
        
    }

    /**
     * @Route("/insta/photo/{hashtag}", name="photo_insta")
     */
    public function picture(Request $request,$hashtag) {
        
        $repository = $this->getDoctrine()->getRepository(Insta::class);
        //$data = $repository->findBy(array(), array('created_time' => "DESC"));
        $search = $request->request->get('search');

        $images = $repository->research("%".$hashtag."%");

        /*$i = 0;
        $images = array();
        foreach($data as $image) {
            if(!empty($image->getLowResolution()))    {
                $array = ['image' => $image->getLowResolution(),
                    'timestamp' => date('d/m/y', $image->getCreatedTime()).' à '.date('H:i:s', $image->getCreatedTime()) ];
                array_push($images, $array);
                $i++;
            }
            if($i === 6)     {
                break;
            }  
            */
        dump($images);
        return $this->render('cache_insta/photos.html.twig', ['images' => $images,'hashtag'=> $hashtag]);

    }

    /**
     * @Route("/insta/hashtag/{hashtag}", name="hashtag_insta")
     */
    public function hashtag(InstaRepository $instaRepository, $hashtag) {

        //zonage en 10x10
        //(44 - 42.8) / 10    = 0.12
        //(7 - 4) / 10        = 0.3

        $em = $this->getDoctrine()->getManager();
        $urlApi="https://api.instagram.com/v1/tags/$hashtag/media/recent?access_token=8439782101.da06fb6.51b1b6f220384c2c8a7e7318cc33ab65";
        $txtJsonResponse = file_get_contents($urlApi);
        $tabJson = json_decode($txtJsonResponse, true);
        $tabData = $tabJson["data"] ?? [];

        foreach ($tabData as $index => $tabPost) {
            $postCaption = $tabPost["caption"]["text"] ?? "";
            $postLatitude = $tabPost["location"]["latitude"] ?? null;
            $postLongitude = $tabPost["location"]["longitude"] ?? null;
            $postLink = $tabPost["link"] ?? "";
            $postId = $tabPost["id"] ?? 0;
            $postTags = implode(",",$tabPost['tags']) ?? "";
            $postUsername = $tabPost["user"]["username"] ?? "";
            $postCreatedTime = $tabPost["created_time"] ?? "";
            $postImageThumbnail = $tabPost["images"]["thumbnail"]["url"] ?? "";
            $postImageLR = $tabPost["images"]["low_resolution"]["url"] ?? "";
            $postImageSR = $tabPost["images"]["standard_resolution"]["url"] ?? "";
            $postLikes = $tabPost['likes']['count'] ?? 0;

            $objInstaPost = $instaRepository->findOneBy(["insta_id" => $postId]);

            if ($objInstaPost == null) {

                //if($postLatitude!=null AND $postLongitude!=null)
                //{
                    // NEW INSTA
                    $objInstaPost = new Insta;

                    $objInstaPost->setLatitude($postLatitude);
                    $objInstaPost->setLongitude($postLongitude);
                    $objInstaPost->setInstaType("insta");
                    $objInstaPost->setLink($postLink);
                    $objInstaPost->setInstaId($postId);
                    $objInstaPost->setTags($postTags);
                    $objInstaPost->setUserUsername($postUsername);
                    $objInstaPost->setCreatedTime($postCreatedTime);
                    $objInstaPost->setThumbnail($postImageThumbnail);
                    $objInstaPost->setLowResolution($postImageLR);
                    $objInstaPost->setStandardResolution($postImageSR);
                    $objInstaPost->setLikes($postLikes);
                    $objInstaPost->setCaption($postCaption);
                    $em->persist($objInstaPost);
                //}

            }
            else {
                // UPDATE DATA
                $objInstaPost->setLatitude($postLatitude);
                $objInstaPost->setLongitude($postLongitude);
                $objInstaPost->setInstaType("insta");
                $objInstaPost->setLink($postLink);
                $objInstaPost->setInstaId($postId);
                $objInstaPost->setTags($postTags);
                $objInstaPost->setUserUsername($postUsername);
                $objInstaPost->setCreatedTime($postCreatedTime);
                $objInstaPost->setThumbnail($postImageThumbnail);
                $objInstaPost->setLowResolution($postImageLR);
                $objInstaPost->setStandardResolution($postImageSR);
                $objInstaPost->setLikes($postLikes);
                $objInstaPost->setCaption($postCaption);
                $em->persist($objInstaPost);
                $em->flush($objInstaPost);
            }

        }
        $em->flush($objInstaPost);

        return $this->render('cache_insta/index.html.twig');
    }

    /**
     * @Route("/insta/search", name="hashtag_Search")
     */
    public function hashtagSearch() {

        return $this->render('cache_insta/hashtag.html.twig');

    }

    /**
     * @Route("/info/api", name="info_api")
     */
    public function infoApi(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Member::class);
        $member = $repository->findOneBy(['username' => 'visiteur']);

        $info = new Info();

        $id = $info->getId();

        $lat = $request->get('lat');
        $long = $request->get('long');
        $title = $request->get('title',"");
        $description = $request->get('description');
        $icone = $request->get('icone');
        $file = $request->files;

        $em = $this->getDoctrine()->getManager();
        $info->setLatitude($lat);
        $info->setLongitude($long);
        $info->setTitle($title);
        $info->setDescription($description);
        $info->setIcon($icone);
        $info->setMember($member);

        $em->persist($info);
        $em->flush();

        $id = $info->getId();

        $tabReponseJson = [];
        $tabReponseJson["message"] = "201";
        $tabReponseJson["id"] = $id;

        return new JsonResponse($tabReponseJson);

    }
}



