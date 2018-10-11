<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use App\Repository\MemberRepository;
use App\Repository\CloudMessageRepository;
use App\Entity\CloudMessage;

class FirebaseController extends AbstractController
{
    /**
     * @Route("/server.php", name="firebase_server")
     */
    public function index(Request $request, CloudMessageRepository $cmRepository, MemberRepository $memberRepository, \Symfony\Component\Asset\Packages $assetsManager)
    {
        $json   = json_encode($_REQUEST);
        $date   = date("H:i:s");
        $ip     = $_SERVER["REMOTE_ADDR"];
        $ua     = $_SERVER["HTTP_USER_AGENT"];
        $title  = "bienvenue";
        $body   = "Hello";
        
        $token  = $_REQUEST["token"] ?? "";
        if ($token != "")
        {
            $userId = $request->get("userId");
            if ($userId > 0) {
                $status = "OK";
                $member = $memberRepository->find($userId);
                if ($member != null) {
                    $status = "MEMBER_ID";
                    $body   = "Hello " . $member->getUsername();
                }
                else {
                    $status = "MEMBER_UNKNOWN";
                }
                
                $em = $this->getDoctrine()->getManager();
                $cloudMessage = $cmRepository->findOneBy(["idMember" => $userId]);
                if ($cloudMessage == null) {
                    $cloudMessage = new CloudMessage;
                    $em->persist($cloudMessage);
                }
                $cloudMessage->setDateSubscription(new \DateTime);
                $cloudMessage->setIdMember($userId);
                $cloudMessage->setStatus($status);
                $cloudMessage->setToken($token);
                
                // https://firebase.google.com/docs/reference/admin/node/admin.messaging.NotificationMessagePayload#icon
                // https://stackoverflow.com/questions/36163803/how-to-get-assets-img-url-in-symfony-controller
                $homeUrl = $this->generateUrl('home', [], UrlGeneratorInterface::ABSOLUTE_URL);
                $homeUrl = str_replace("http://", "https://", $homeUrl);
                $iconUrl = $homeUrl . "assets/img/icon/icon96.png";
                $log =
<<<LOG
[$date][$ip][$userId]
[$ua]
===
$json
===
curl -X POST -H "Authorization: key=AAAApmvRoqw:APA91bHvEThYWKVTRin6-tjcUZwYABWw9j0carNoKjosSFirR9E1VQ3q7-RGHm7bPqJ5aSu21mY2a1uVFw3Nmgp_sYZTkGS-LVREPyMo4iCeiDB15Pkj_X5HAyZI9J2yVK-qD0MzxaYy" -H "Content-Type: application/json" -d '{
  "notification": {
    "title": "$date",
    "body": "$body",
    "icon": "$iconUrl",
    "click_action": "https://myprovence.code4marseille.fr"
  },
  "to": "$token",
}' "https://fcm.googleapis.com/fcm/send"

===

LOG;
                $cloudMessage->setLog($log);
                
                $em->flush();
            }
        
        }


        $result = $this->sendMessage2($title, $body, $token, $iconUrl);
        
        return new JsonResponse([ "result" => $result]);
    }
    
    
    public function sendMessage2 ($title, $body, $token, $iconUrl)
    {
        ob_start();
        
        $curlPath = trim(shell_exec("which curl"));
        echo $curlPath;
        
        // FIXME... DANGEROUS...
        $cmd =
<<<LOG
$curlPath -X POST -H "Authorization: key=AAAApmvRoqw:APA91bHvEThYWKVTRin6-tjcUZwYABWw9j0carNoKjosSFirR9E1VQ3q7-RGHm7bPqJ5aSu21mY2a1uVFw3Nmgp_sYZTkGS-LVREPyMo4iCeiDB15Pkj_X5HAyZI9J2yVK-qD0MzxaYy" -H "Content-Type: application/json" -d '{
  "notification": {
    "title": "$title",
    "body": "$body",
    "icon": "$iconUrl",
    "click_action": "https://myprovence.code4marseille.fr"
  },
  "to": "$token",
}' "https://fcm.googleapis.com/fcm/send"
LOG;
        $resultCurl = shell_exec($cmd);
        echo $resultCurl;
        
        $result = ob_get_clean();
        return $result;
    }
    
    public function sendMessage($title, $body, $token)
    {
        // FIXME: PB DE HEADER AUTH...
        
        $homeUrl = $this->generateUrl('home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $homeUrl = str_replace("http://", "https://", $homeUrl);
        $iconUrl = $homeUrl . "assets/img/icon/icon96.png";

        // https://lornajane.net/posts/2011/posting-json-data-with-php-curl
        $tabData = [
            "to"    => $token,
            "notification" => [
                    "title"         => "$title",
                    "body"          => "$body",
                    "icon"          => "$iconUrl",
                    "click_action"  => "https://myprovence.code4marseille.fr",
                ],
            ];
        $jsonText = json_encode($tabData);
        
        
        $tabHeader = [];
        
        $urlTarget   = "https://fcm.googleapis.com/fcm/send";
        $key         = "AAAApmvRoqw:APA91bHvEThYWKVTRin6-tjcUZwYABWw9j0carNoKjosSFirR9E1VQ3q7-RGHm7bPqJ5aSu21mY2a1uVFw3Nmgp_sYZTkGS-LVREPyMo4iCeiDB15Pkj_X5HAyZI9J2yVK-qD0MzxaYy";

        $tabHeader[] = "Content-Type: application/json";
        $tabHeader[] = "Authorization: key=$key";
        
        // http://www.php.net/manual/fr/function.curl-setopt.php
        // initialisation de la session
        $ch = curl_init();
        
        // configuration des options
        curl_setopt($ch, CURLOPT_URL,           $urlTarget);
        curl_setopt($ch, CURLOPT_HTTPAUTH,      CURLAUTH_ANY);

        // curl_setopt($ch, CURLOPT_HEADER,        $tabHeader);
        curl_setopt($ch, CURLOPT_HEADER,        "Authorization: key=$key");
        curl_setopt($ch, CURLOPT_POST,          true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,    $jsonText);

        // exécution de la session
        curl_exec($ch);
        
        // fermeture des ressources
        curl_close($ch);

    }
}
