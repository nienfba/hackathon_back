<?php

namespace App\Controller;

use App\Entity\PrivateGroup;
use App\Form\PrivateGroupType;
use App\Repository\PrivateGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/private-group")
 */
class PrivateGroupController extends AbstractController
{
    /**
     * @Route("/", name="private_group_index", methods="GET")
     */
    public function index(PrivateGroupRepository $privateGroupRepository): Response
    {
        return $this->render('private_group/index.html.twig', ['private_groups' => $privateGroupRepository->findAll()]);
    }

    /**
     * @Route("/new", name="private_group_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $privateGroup = new PrivateGroup();
        $form = $this->createForm(PrivateGroupType::class, $privateGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($privateGroup);
            $em->flush();

            return $this->redirectToRoute('private_group_index');
        }

        return $this->render('private_group/new.html.twig', [
            'private_group' => $privateGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="private_group_show", methods="GET")
     */
    public function show(PrivateGroup $privateGroup): Response
    {
        return $this->render('private_group/show.html.twig', ['private_group' => $privateGroup]);
    }

    /**
     * @Route("/{id}/edit", name="private_group_edit", methods="GET|POST")
     */
    public function edit(Request $request, PrivateGroup $privateGroup): Response
    {
        $form = $this->createForm(PrivateGroupType::class, $privateGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('private_group_edit', ['id' => $privateGroup->getId()]);
        }

        return $this->render('private_group/edit.html.twig', [
            'private_group' => $privateGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="private_group_delete", methods="DELETE")
     */
    public function delete(Request $request, PrivateGroup $privateGroup): Response
    {
        if ($this->isCsrfTokenValid('delete'.$privateGroup->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($privateGroup);
            $em->flush();
        }

        return $this->redirectToRoute('private_group_index');
    }
}
