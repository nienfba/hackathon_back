<?php

namespace App\Controller;

use App\Entity\Info;
use App\Entity\Member;
use App\Entity\Contact;
use App\Entity\Message;
use App\Entity\Category;
use App\Service\Upload;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Form\MessageType;
use App\Form\ContactType;
use App\Repository\InfoRepository;
use App\Repository\CategoryRepository;
use App\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use App\Mail\MyMailer;

class PublicZoneController extends AbstractController
{
    /**
     * @Route("/old", name="homeold", methods="GET|POST")
     */
    public function index(InfoRepository $infoRepository, CategoryRepository $CategoryRepository, Request $request)
    {
        $listeCategorie = $CategoryRepository->findAll();

        $limit = intval($request->request->get('limit') ?? 12);
        $offset = intval($request->request->get('offset') ?? 0);
        $tabInfo = $infoRepository->dernieresInfos($limit, $offset);

        if ($request->isXmlHttpRequest()){
            return $this->json($tabInfo);
        }else{
            return $this->render('public_zone/old.html.twig', [
                'tabInfo' => $tabInfo,
                'limit' => $limit,
                'listeCategorie' => $listeCategorie,
            ]);
        }
    }

    /**
     * @Route("/", name="home", methods="GET|POST")
     */
    public function indexfront()
    {
        return $this->render('public_zone/index.html.twig', []);
    }

    /**
     * @Route("/info-public/{id}", name="info-public")
     */
    public function infoPublic(Info $info, MessageRepository $messageRepository, Request $request, AuthorizationCheckerInterface $authChecker, MyMailer $myMailer)
    {   
        if ($authChecker->isGranted('IS_AUTHENTICATED_FULLY') 
            && ($request->request->get('formCommentaire') ?? false) 
            && ($request->request->get('commentaire'))){

            $currentUser = $this->getUser();
            
            // IL FAUT ETRE CONNECTE POUR LAISSER UN MESSAGE
            if ($currentUser !=null) {
                $message = new Message;
                
                $commentaire = $request->request->get('commentaire');
                $commentaire = "@".$info->getMember()->getUsername(). " " .$commentaire;
                $message->setContent($commentaire);
                
                $message->setPublicationDate(new \DateTime);
                $message->setStatus("visible");
                $message->setInfo($info);
                $message->setAuthor($currentUser);
                $message->setIp($request->getClientIp());
    
                $em = $this->getDoctrine()->getManager();
                $em->persist($message);
                $em->flush();
                
                // ENVOI DE MAIL AU MEMBRE QUI A CREE L'INFO
                $author = $info->getMember();
                if ($author != null) {
                    $infoTitle      = $info->getTitle();
                    $infoId         = $info->getId();
                    
                    $to             = $author->getEmail();
                    $toUsername     = $author->getUsername();
                    $fromUsername   = $currentUser->getUsername();
                    $subject        = "[MyProvence] message de $fromUsername";
                    $body           = 
<<<BODYMAIL

Bonjour $toUsername,

Suite à votre publication:
$infoTitle

Vous avez reçu un message de "$fromUsername":
===
$commentaire
===

Pour répondre cliquez sur le lien ci-dessous:
.../TODO/$infoId

BODYMAIL;
                    $myMailer->sendMail($to, $subject, $body);
                }
                
            }
            
        }

        $limit = intval($request->request->get('limit') ?? 10);
        $finMessage = false;
        $offset = intval($request->request->get('offset') ?? 0);
        $listeMessage = $messageRepository->tousMessages($info->getId(), $limit, $offset);
        if ($limit == 0 || !$listeMessage || count($listeMessage)<$limit){$finMessage = true;}

        if ($request->isXmlHttpRequest()){
            return $this->json(['info' => $info, "listeMessage" => $listeMessage, "finMessage" => $finMessage]);
        }else{
            return $this->render('public_zone/info.html.twig', ['info' => $info, 'listeMessage' => $listeMessage, 'finMessage' => $finMessage]);
        }
    }

    /**
     * @Route("/member-public/{id}", name="member-public")
     */
    public function memberPublic(Request $request, Member $member, MyMailer $myMailer, Upload $upload)
    {
        $em         = $this->getDoctrine()->getManager();
        $message    = new Message();
        $form       = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        $messageRetour = "";
        if ($form->isSubmitted() && $form->isValid()) {

            $ip          = $this->ip();
            $pattern     = "@".$member->getUsername();
            $content     = $form['content']->getData();
            $currentUser = $this->getUser();

            $message->setAuthor($currentUser);
            $message->setIp($ip);
            $message->setStatus("ok");
            if(preg_match("#".$pattern."#",$content))
            {
                $this->image($upload,$message);
                $em->persist($message);
                $em->flush();
            }
            else
            {
                $this->image($upload,$message);
                $content = $pattern ." ".$content;
                $message->setContent($content);
                $em->persist($message);
                $em->flush();
            }
            
            // ENVOI D'UN  MAIL POUR PREVENIR LE MEMBRE
            $to             = $member->getEmail();
            $toUsername     = $member->getUsername();
            $fromUsername   = $currentUser->getUsername();
            $subject        = "[MyProvence] message de $fromUsername";
            $body           = 
<<<BODYMAIL

Bonjour $toUsername,

Vous avez reçu un message de "$fromUsername":
===
$content
===

Pour répondre cliquez sur le lien ci-dessous:
.../TODO/member/$toUsername

BODYMAIL;
            
            $myMailer->sendMail($to, $subject, $body);
            // ON RESTE SUR LA PAGE ET AFFICHER UN MESSAGE DE CONFIRMATION
            // return $this->redirectToRoute('member-public',['id' => $member->getId()]);
            
            $messageRetour = "Votre message a bien été envoyé à $toUsername.";
        }
        return $this->render('public_zone/member.html.twig', [
                                'member'            => $member,
                                'form'              => $form->createView(),
                                'messageRetour'     => $messageRetour,
                                ]);
    }
    
    /**
     * @Route("/actus/{urlCategorie}", name="actus")
     */
    public function actus($urlCategorie = null, InfoRepository $infoRepository, CategoryRepository $CategoryRepository, Request $request)
    {
        if ($urlCategorie == null){
            $currentCategory = null;
            $tabInfo = $infoRepository->findBy([], [ "publicationDate" => "DESC"]);
        }else{
            $currentCategory = $CategoryRepository->findOneBy(["urlName" => $urlCategorie]);
            $tabInfo = $infoRepository->infoFiltre($urlCategorie);
        }

        if ($request->isXmlHttpRequest()){
            return $this->json(["tabInfo" => $tabInfo, "currentCategory" => $currentCategory]);
        }else{
            return $this->render("public_zone/actus.html.twig", ["tabInfo" => $tabInfo, "currentCategory" => $currentCategory]);
        }

    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request, ObjectManager $manager, MyMailer $myMailer)
    {
        $contact = new Contact;     
        
        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {       
            if(!$contact->getId()){
                $contact->setReceptionDate(new \DateTime());
            }
            
            $manager->persist($contact);
            $manager->flush();
            
            $name = $form['name']->getData();
            $email = $form['email']->getData();
            $subject = $form['subject']->getData();
            $message = $form['message']->getData();
            
            //Récupérer la véritable adresse IP d'un visiteur:

            $ip=$this->ip();

            # set form data 
            $contact->setName($name);
            $contact->setEmail($email);          
            $contact->setSubject($subject);     
            $contact->setMessage($message);
            $contact->setIp($ip);
            
            # finally add data in database
            $sn = $this->getDoctrine()->getManager();      
            $sn -> persist($contact);
            $sn -> flush();

            //Envoi du mail 
            /*
            $mail = (new \Swift_Message('My Provence'))
            ->setFrom($email)
            ->setTo('c4mars@gmail.com')
            ->setBody($this->renderView('public_zone/registrationmail.html.twig',array('contact' => $contact)),'text/html');

            //Mailer a utiliser dans le .env
            // MAILER_URL=gmail://c4mars@gmail.com:weekend@2018@localhost?encryption=tls&auth_mode=oauth            
            $mailer->send($mail);
            */
            
            $body = $this->renderView('public_zone/registrationmail.html.twig', array('contact' => $contact));
            $myMailer->sendMail('c4mars@gmail.com', '[contact] My Provence', $body);
            
            // return $this->redirectToRoute('confirmationContact');
            return $this->render('public_zone/confirmationContact.html.twig',['name' => $name]);

        }

        return $this->render('public_zone/contact.html.twig',[
            'form'=> $form->createView()
        ]);
    }

    /**
     * @Route("/confirmationContact", name="confirmationContact")
     */
    public function confirmationContact()
    {
        return $this->render('public_zone/confirmationContact.html.twig'); 
    }

    public function ip(){
        //Récupérer la véritable adresse IP d'un visiteur:
        // IP si internet partagé
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        // IP derrière un proxy
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // Sinon : IP normale
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    public function image($upload,$message)
    {
        $filename =  md5(uniqid());  //génération d'un nom de fichier hashé
        $userDir = "/" . $this->getUser()->getId();
        // création des 3 repertoires d'upload des médias
        $upload->directory($userDir);
        $upload->upload($filename,$userDir,$message);
    }
}
