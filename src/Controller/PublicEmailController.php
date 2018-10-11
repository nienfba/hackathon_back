<?php

namespace App\Controller;

use App\Mail\MyMailer;
use App\Entity\Email;
use App\Repository\EmailRepository;
use App\Repository\InfoRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicEmailController extends AbstractController
{
    /**
     * @Route("/public/email", name="public_email")
     */
    public function index()
    {
        return $this->render('public_email/index.html.twig', [
            'controller_name' => 'PublicEmailController',
        ]);
    }

    /**
     * @Route("/public/signaler", name="signaler")
     */
    public function signaler(MyMailer $myMailer, Request $request, InfoRepository $infoRepository, MessageRepository $messageRepository)
    {
        $em = $this->getDoctrine()->getManager();

        $email = new Email();
        $emailTo = ["c4mars@gmail.com"];
        switch ($request->request->get("formSignaler")) {
            case "info":
                $info = $infoRepository->find(intval($request->request->get("idInfoSignaler")));

                $emailSubject = "[SIGNALEMENT INFO] ID_INFO : " . $info->getId() . " ID_AUTHEUR_SIGNALEMENT : " . $this->getUser()->getUsername() . " (ID : " . $this->getUser()->getId();
                $emailContent = "<<< SIGNALEMENT INFO >>><br>"
                                ."<br>"
                                ."AUTHEUR_SIGNALEMENT : " . $this->getUser()->getUsername() . " (ID : " . $this->getUser()->getId() .")<br>"
                                ."COMMENTAIRE_SIGNALEMENT : " . $request->request->get("contentSignaler") . "<br>"
                                ."<br>"
                                ."ID_INFO : " . $info->getId() . "<br>"
                                ."TITRE_INFO : " . $info->getTitle() . "<br>"
                                ."CONTENU_INFO : " . $info->getDescription() . "<br>"
                                ."ID_AUTHEUR_INFO : " . $info->getMember()->getId() ."<br>"
                                ."AUTHEUR_INFO : " . $info->getMember()->getUsername() ."<br>"
                                ;
                
                $info->setStatus("A VERIFIER");
                $em->persist($info);

                $email->setEmailType("signalement info");

                break;
            
            case "message":
                $message = $messageRepository->find(intval($request->request->get("idMessageSignaler")));
                $info = $infoRepository->find(intval($request->request->get("idInfoSignaler")));
                
                $emailSubject = "[SIGNALEMENT MESSAGE] ID_MESSAGE : " . $message->getId() . " ID_AUTHEUR_SIGNALEMENT : " . $this->getUser()->getUsername() . " (ID : " . $this->getUser()->getId();
                $emailContent = "<<< SIGNALEMENT MESSAGE >>><br>"
                                ."<br>"
                                ."AUTHEUR_SIGNALEMENT : " . $this->getUser()->getUsername() . " (ID : " . $this->getUser()->getId() .")<br>"
                                ."COMMENTAIRE_SIGNALEMENT : " . $request->request->get("contentSignaler") . "<br>"
                                ."<br>"
                                ."ID_MESSAGE : " . $message->getId() . "<br>"
                                ."CONTENU_MESSAGE : " . $message->getContent() . "<br>"
                                ."ID_AUTHEUR_MESSAGE : " . $message->getAuthor()->getId() ."<br>"
                                ."AUTHEUR_MESSAGE : " . $message->getAuthor()->getUsername() ."<br>"
                                ."IP_AUTHEUR_MESSAGE : " . $message->getIp() ."<br>"
                                ."<br>"
                                ."ID_INFO : " . $info->getId() . "<br>"
                                ."TITRE_INFO : " . $info->getTitle() . "<br>"
                                ."CONTENU_INFO : " . $info->getDescription() . "<br>"
                                ."AUTHEUR_INFO : " . $info->getMember()->getUsername() ."<br>"
                                ;

                $message->setStatus("A VERIFIER");
                $em->persist($message);

                $email->setEmailType("signalement message");

                break;
        }
        
        $sendMailReturn = $myMailer->sendMail ($emailTo[0], $emailSubject, $emailContent);

        if ($sendMailReturn == ""){
            $email  ->setEmailFrom($myMailer->getFromEmail())
                    ->setEmailTo($emailTo)
                    ->setEmailSubject($emailSubject)
                    ->setEmailContent($emailContent);
                    $em->persist($email);
        }

        $em->flush();

        if ($request->isXmlHttpRequest()){
            return $this->json(['signaler' => true]);
        }else{
            return $this->render('public_zone/signaler.html.twig');
        }
    }

}
