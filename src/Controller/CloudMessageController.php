<?php

namespace App\Controller;

use App\Entity\CloudMessage;
use App\Form\CloudMessageType;
use App\Repository\CloudMessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/cloud/message")
 */
class CloudMessageController extends AbstractController
{
    /**
     * @Route("/", name="cloud_message_index", methods="GET")
     */
    public function index(CloudMessageRepository $cloudMessageRepository): Response
    {
        return $this->render('cloud_message/index.html.twig', ['cloud_messages' => $cloudMessageRepository->findAll()]);
    }

    /**
     * @Route("/new", name="cloud_message_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $cloudMessage = new CloudMessage();
        $form = $this->createForm(CloudMessageType::class, $cloudMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($cloudMessage);
            $em->flush();

            return $this->redirectToRoute('cloud_message_index');
        }

        return $this->render('cloud_message/new.html.twig', [
            'cloud_message' => $cloudMessage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cloud_message_show", methods="GET")
     */
    public function show(CloudMessage $cloudMessage): Response
    {
        return $this->render('cloud_message/show.html.twig', ['cloud_message' => $cloudMessage]);
    }

    /**
     * @Route("/{id}/edit", name="cloud_message_edit", methods="GET|POST")
     */
    public function edit(Request $request, CloudMessage $cloudMessage): Response
    {
        $form = $this->createForm(CloudMessageType::class, $cloudMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('cloud_message_edit', ['id' => $cloudMessage->getId()]);
        }

        return $this->render('cloud_message/edit.html.twig', [
            'cloud_message' => $cloudMessage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cloud_message_delete", methods="DELETE")
     */
    public function delete(Request $request, CloudMessage $cloudMessage): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cloudMessage->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($cloudMessage);
            $em->flush();
        }

        return $this->redirectToRoute('cloud_message_index');
    }
}
