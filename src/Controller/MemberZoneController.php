<?php

namespace App\Controller;

use App\Entity\Info;
use App\Entity\Member;
use App\Entity\PrivateGroup;
use App\Entity\PrivateGroupMember;
use App\Form\PrivateGroupType;
use App\Form\InfoType;
use App\Entity\Message;
use App\Form\MemberEditType;
use App\Repository\InfoRepository;
use App\Repository\MemberRepository;
use App\Repository\PrivateGroupMemberRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Service\Upload;

/**
 * @Route("/member")
 */
class MemberZoneController extends AbstractController
{
    /**
     * @Route("/", name="member_zone")
     */
    public function index(InfoRepository $infoRepository, MemberRepository $memberRepository, PrivateGroupMemberRepository $privateGroupMemberRepository)
    {
        // récupération des infos du membre connecté
        $id=$this->getUser()->getId();
        $tabInfo = $infoRepository->findBy(['member' => $id]);
        $member = $memberRepository->find($id);

        $listPrivateGroups = $privateGroupMemberRepository->privateGroupForUser($this->getUser());
        
        return $this->render('member_zone/index.html.twig', [
            'member' => $member,
            'tabInfo' => $tabInfo,
            'listPrivateGroups' => $listPrivateGroups,
            ]);
    }

    /**
     * @Route("/new-info", name="new-info")
     */
    public function newinfo(Request $request, Upload $upload): Response
    {
        $em = $this->getDoctrine()->getManager();
        $info = new Info();
        $form = $this->createForm(InfoType::class, $info);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $media = $info->getMedia();
            $filename =  md5(uniqid());  //génération d'un nom de fichier hashé
            $userDir = "/" . $this->getUser()->getId();
            // création des 3 repertoires d'upload des médias
            $upload->directory($userDir);

            // on recupere la liste des images téléchargées pour les stocker
            // et les mettre au bon format
            foreach ($media as $file) {
                $upload->upload($filename,$userDir,$file);
            }

            $info->setMember($this->getUser());
            $em->persist($info);
            $em->flush();

            return $this->redirectToRoute('member_zone');
        }

        return $this->render('member_zone/newinfo.html.twig', [
            'info' => $info,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit", name="member-edit", methods="GET|POST")
     */
    public function memberEdit(Request $request, MemberRepository $memberRepository, Upload $upload): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $password = $this->getDoctrine()
        ->getRepository(Member::class)
        ->find($user->getId());

        $passwordSave = clone $password;

        $member = New Member();
        $form = $this->createForm(MemberEditType::class, $this->getUser());
        $form->handleRequest($request);
        
        $newpassword = $form['password']->getData();
        $passwordHash = password_hash($newpassword, PASSWORD_BCRYPT);

        if ($form->isSubmitted() && $form->isValid()){

            $user->setUsername($form['username']->getData());

            if($user->getPassword()==''){
                $user->setPassword($passwordSave->getPassword());
            }
            else{                         
                $user->setPassword($passwordHash);     
            }

            $userDir = "/" . $this->getUser()->getId();
            $filename =  md5(uniqid());  //génération d'un nom de fichier hashé
            // création des 3 repertoires d'upload des médias
            //$this->directory($userDir);
            $upload->directory($userDir);

            if(!is_string($user->getFile()))
            {
                //$this->upload($filename,$userDir,$user);
                $upload->upload($filename,$userDir,$user);
            };

            $em->persist($user);
            $em->flush();

            $response = $this->render('member_zone/messageEditOk.html.twig');
            // POUR DEBLOQUER LE JS DANS UN TEXTAREA
            $response->headers->set("X-XSS-Protection", "0");
            return $response;
            //return $this->redirectToRoute('login');
        }

        return $this->render('member_zone/member-edit.html.twig',['form' => $form->createView()]);
       
    }

    /**
     * @Route("/info/edit/{id}", name="member-info-edit", methods="GET|POST")
     */
    public function edit(Request $request, Info $info, Upload $upload): Response
    {
        $form = $this->createForm(InfoType::class, $info);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $media = $info->getMedia();
            $userDir = "/" . $this->getUser()->getId();

            // création des 3 repertoires d'upload des médias
            $upload->directory($userDir);

            $filename =  md5(uniqid());  //génération d'un nom de fichier hashé

            // on recupere la liste des images téléchargées pour les stocker
            // et les mettre au bon format
            foreach ($media as $file)
            {
                if(!is_string($file->getFile()))
                {
                    $upload->upload($filename,$userDir,$file);
                }
            }

            $info->setPublicationDate(new \datetime());

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('member_zone', ['id' => $info->getId()]);
        }

        return $this->render('member_zone/infoedit.html.twig', [
            'info' => $info,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete-info/{id}", name="delete-info")
     */
    public function delete(Request $request, Info $info): Response
    {
        if ($this->isCsrfTokenValid('delete'.$info->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($info);
            $em->flush();
        }

        return $this->redirectToRoute('member_zone');
    }

    /**
     * @Route("/member-message", name="member-message")
     */
    public function message()
    {
        $repository = $this->getDoctrine()->getRepository(Message::class);

        $messageSent = $repository->findBy(
            ['Author' => $this->getUser()->getId()], ['publicationDate' => 'DESC']);

        $member ="%@".$this->getUser()->getUsername()."%";

        $messageReceived = $repository->Search($member);
        
        return $this->render('member_zone/member-message.html.twig',
            [ 'messagesent' => $messageSent,
              'messagereceived' => $messageReceived
            ]);
    }

    /**
     * @Route("/message/delete", name="message-delete")
     */
    public function msgDelete(Request $request)
    {
        // ON VA RENVOYER UNE REPONSE EN JSON
        // https://api.symfony.com/4.0/Symfony/Component/HttpFoundation/JsonResponse.html
        $msg=  $request->request->get('msg');
        $tabReponseJson = [];
        $tabReponseJson["message"] = $msg;

        $repository = $this->getDoctrine()->getRepository(Message::class);
        $message = $repository->find(str_replace("msg","",$msg));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($message);
        $entityManager->flush();

        return new JsonResponse($tabReponseJson);
    }

}