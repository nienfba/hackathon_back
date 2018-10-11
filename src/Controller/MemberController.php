<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\MemberType;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/member")
 */
class MemberController extends AbstractController
{
    /**
     * @Route("/", name="member_index", methods="GET")
     */
    public function index(MemberRepository $memberRepository): Response
    {   
        //renvois vers la page index du CRUD et affiche l'ensemble des menber inscrit
        return $this->render('member/index.html.twig', ['members' => $memberRepository->findAll()]);
    }

    /**
     * @Route("/new", name="member_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {   
        //
        $member = new Member();

        //
        $form = $this->createForm(MemberType::class, $member);

        //
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            //............Hachage du mot de passe
            $password = $member->getPassword();
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $member->setPassword($passwordHash);

            //............Atribution d'un rôle ADMIN par default...à revoir!!!
            $member->setRole('ROLE_ADMIN');

            //
            $em = $this->getDoctrine()->getManager();

            //
            $em->persist($member);
            $em->flush();

            //Renvois vers la page index du crud donc vertion plutot admin ...à revoir!!!
            return $this->redirectToRoute('member_index');
        }

        //Renvois vers la page de creation d'un menber ( version crud donc a modifier..creation d'une page d'insceiption necessaire) ...à revoir!!!
        return $this->render('member/newinfo.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="member_show", methods="GET")
     */
    public function show(Member $member): Response
    {
        return $this->render('member/show.html.twig', ['member' => $member]);
    }

    /**
     * @Route("/{id}/edit", name="member_edit", methods="GET|POST")
     */
    public function edit(Request $request, Member $member): Response
    {
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('member_edit', ['id' => $member->getId()]);
        }

        return $this->render('member/edit.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="member_delete", methods="DELETE")
     */
    public function delete(Request $request, Member $member): Response
    {
        if ($this->isCsrfTokenValid('delete'.$member->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($member);
            $em->flush();
        }

        return $this->redirectToRoute('member_index');
    }
}
