<?php

namespace App\Controller;

use App\Entity\Insta;
use App\Form\InstaType;
use App\Repository\InstaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
 
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * @Route("/admin/insta")
 */
class InstaController extends AbstractController
{
    /**
     * @Route("/", name="insta_index", methods="GET")
     */
    public function index(InstaRepository $instaRepository): Response
    {
        return $this->render('insta/index.html.twig', ['instas' => $instaRepository->findAll()]);
    }

    /**
     * @Route("/search", name="insta_search", methods="GET|POST")
     */
    public function search (Request $request, InstaRepository $instaRepository) 
    {
        
        // https://www.instagram.com/developer/endpoints/media/
        // https://api.instagram.com/v1/locations/search?lat=43.3&lng=5.4&access_token=8439782101.da06fb6.51b1b6f220384c2c8a7e7318cc33ab65
        /*
        zonage en 10x10
        (44 - 42.8) / 10    = 0.12
        (7 - 4) / 10        = 0.3
                        
        */
        $lat        = floatval($request->get("lat",      $_GET['lat']));
        $lng        = floatval($request->get("lng",      $_GET['lng']));
        
        // $distance   = $request->get("distance", 5000);
        $distance   = 5000;     // ON FORCE LA DISTANCE MAX POUR OBTENIR UNE COUVERTURE MAXIMALE
        
        $latRef     = intval($lat * 10) * 0.1;
        $lngRef     = intval($lng * 20) * 0.05;
        
        $tabResponse = [];
        
        $objInsta   = $instaRepository->findOneBy([ "insta_type" => "API", "latitude" => $latRef, "longitude" => $lngRef ]);
        $em = $this->getDoctrine()->getManager();
        if ($objInsta != null) {
            $txtJsonResponse    = $objInsta->getResponseJson();
            $dateCreation       = $objInsta->getCreatedTime();
            $timeCreation       = strtotime($dateCreation);
            $now                = time();
            
            // TEST SI LA DERNIERE REQUETE EST TROP VIEILLE (300s => 5min)
            if ($now - $timeCreation > 300) {
                // ON OUBLIE LA DERNIERE REPONSE
                $em->remove($objInsta);
                $em->flush();

                // ET ON VA RAFRAICHIR LA REPONSE
                $objInsta = null;
            }
        }
        
        if ($objInsta == null) {
            $urlApi             = "https://api.instagram.com/v1/media/search?lat=$latRef&lng=$lngRef&distance=$distance&access_token=8439782101.da06fb6.51b1b6f220384c2c8a7e7318cc33ab65";
            $txtJsonResponse    = file_get_contents($urlApi);
            
            if ($txtJsonResponse) {

                $objInsta = new Insta;
                $objInsta->setLatitude($latRef);
                $objInsta->setLongitude($lngRef);
                $objInsta->setResponseJson($txtJsonResponse);
                $objInsta->setInstaId(0);
                $objInsta->setInstaType("API");
                
                $em->persist($objInsta);


                // EXTRACT ALL POINTS IN JSON RESPONSE                
                $tabJson                = json_decode($txtJsonResponse, true);
                $tabData                = $tabJson["data"] ?? [];
                foreach($tabData as $index => $tabPost) {
                    $postId             = $tabPost["id"] ?? 0;
                    
                    $postLatitude       = $tabPost["location"]["latitude"] ?? 43.3;
                    $postLongitude      = $tabPost["location"]["longitude"] ?? 5.4;
                    $postLink           = $tabPost["link"] ?? "";
                    $postUsername       = $tabPost["user"]["username"] ?? "";
                    $postCreatedTime    = $tabPost["created_time"] ?? "";
                    $postImageThumbnail = $tabPost["images"]["thumbnail"]["url"] ?? "";
                    $postImageLR        = $tabPost["images"]["low_resolution"]["url"] ?? "";
                    $postImageSR        = $tabPost["images"]["standard_resolution"]["url"] ?? "";
                    $postCaption        = $tabPost["caption"]["text"] ?? "";
                    
                    $objInstaPost   = $instaRepository->findOneBy([ "insta_id" => $postId ]);
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
                        
                        $em->persist($objInstaPost);
                        
                    }
                    else {
                        // UPDATE DATA??
                    }
                            
                }
                
                $em->flush();
            }
        }

        $tabJson    = json_decode($txtJsonResponse, true);
        $tabResponse += $tabJson;
        
        return new JsonResponse($tabResponse);
    }

    /**
     * @Route("/new", name="insta_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $instum = new Insta();
        $form = $this->createForm(InstaType::class, $instum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($instum);
            $em->flush();

            return $this->redirectToRoute('insta_index');
        }

        return $this->render('insta/new.html.twig', [
            'instum' => $instum,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="insta_show", methods="GET")
     */
    public function show(Insta $instum): Response
    {
        return $this->render('insta/show.html.twig', ['instum' => $instum]);
    }

    /**
     * @Route("/{id}/edit", name="insta_edit", methods="GET|POST")
     */
    public function edit(Request $request, Insta $instum): Response
    {
        $form = $this->createForm(InstaType::class, $instum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('insta_edit', ['id' => $instum->getId()]);
        }

        return $this->render('insta/edit.html.twig', [
            'instum' => $instum,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="insta_delete", methods="DELETE")
     */
    public function delete(Request $request, Insta $instum): Response
    {
        if ($this->isCsrfTokenValid('delete'.$instum->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($instum);
            $em->flush();
        }

        return $this->redirectToRoute('insta_index');
    }
    
    
}
