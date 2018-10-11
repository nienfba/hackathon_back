<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Info;
use App\Entity\Member;
use App\Entity\PrivateGroup;
use App\Entity\PrivateGroupMember;

use App\Form\PrivateGroupType;

use App\Repository\InfoRepository;
use App\Repository\MemberRepository;
use App\Repository\PrivateGroupRepository;
use App\Repository\PrivateGroupMemberRepository;

/**
 * @Route("/member")
 */

class MemberPrivateGroupController extends AbstractController
{
    /**
     * @Route("/member/private/group", name="member_private_group")
     */
    public function index()
    {
        return $this->render('member_private_group/index.html.twig', [
            'controller_name' => 'MemberPrivateGroupController',
        ]);
    }

    /**
     * @Route("/private-group-create", name="member_private_group_create", methods="GET|POST")
     */
    public function memberPrivateGroupCreate(Request $request, MemberRepository $memberRepository)
    {
        $privateGroup = new PrivateGroup();
        $form = $this->createForm(PrivateGroupType::class, $privateGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($privateGroup);
            $em->flush();
            
            $privateGroupMember = new PrivateGroupMember();
            $privateGroupMember->setPrivateGroup($privateGroup)
                                ->setMember($this->getUser())
                                ->setRole("GROUP_ADMIN");

            $em->persist($privateGroupMember);
            $em->flush();

            return $this->redirectToRoute('member_zone');
        }

        return $this->render('private_group/new.html.twig', [
            'private_group' => $privateGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/private-group-remove", name="member_private_group_remove", methods="GET|POST")
     */
    public function memberPrivateGroupRemove(Request $request, PrivateGroupMemberRepository $privateGroupMemberRepository)
    {
        $privateGroupId = intval($request->request->get('privateGroupId') ?? null);

        if ($privateGroupId != null){
            
            $em = $this->getDoctrine()->getManager();

            $privateGroup = $em->getRepository(PrivateGroup::class)
                                ->find($privateGroupId);

            // Vérification des droits GROUP_ADMIN du user sur le PrivateGroup
            $privateGroupAdmin = $privateGroupMemberRepository->findOneBy(["privateGroup" => $privateGroup, "member" => $this->getUser()]);
            if ($privateGroupAdmin->getRole() == "GROUP_ADMIN"){

                // suppression des membres du PrivateGroup
                $privateGroupMemberRepository->privateGroupRemove($privateGroupId);

                // Suppression du PrivateGroup de l'entité PrivateGroup
                $em->remove($privateGroup);
                $em->flush();
            
            }
        }

        return $this->redirectToRoute('member_zone');
    }

    /**
     * @Route("/private-group-remove-member", name="member_private_group_remove_member", methods="GET|POST")
     */
    public function memberPrivateGroupRemoveMember(Request $request, PrivateGroupMemberRepository $privateGroupMemberRepository)
    {
        $privateGroupId = intval($request->request->get('privateGroupId') ?? null);
        $memberId = intval($request->request->get('memberId') ?? null);
        if ($privateGroupId != null){
            
            $em = $this->getDoctrine()->getManager();

            $privateGroup = $em->getRepository(PrivateGroup::class)
                                ->find($privateGroupId);

            if ($memberId != null){
                // Vérification des droits GROUP_ADMIN du user sur le PrivateGroup
                $privateGroupAdmin = $privateGroupMemberRepository->findOneBy(["privateGroup" => $privateGroup, "member" => $this->getUser()]);
                $nbAdminPrivateGroup = count($privateGroupMemberRepository->findBy(["privateGroup" => $privateGroup, "role" => "GROUP_ADMIN"]));

                if (($privateGroupAdmin->getRole() == "GROUP_ADMIN" && $this->getUser()->getId() != $memberId)
                    || ($privateGroupAdmin->getRole() == "GROUP_ADMIN" && $this->getUser()->getId() == $memberId && $nbAdminPrivateGroup > 1)
                    || ($privateGroupAdmin->getRole() != "GROUP_ADMIN" && $this->getUser()->getId() == $memberId)){

                    // suppression des membres du PrivateGroup
                    $privateGroupMemberRepository->privateGroupRemoveMember($privateGroupId, $memberId);
                }
            }
            $listeMember = $privateGroupMemberRepository->userInPrivateGroup($privateGroup);
        }

        if ($this->getUser()->getId() == $memberId){return $this->redirectToRoute('member_zone');}

        return $this->render('member_private_group/list-member.html.twig', ['privateGroupId' => $privateGroupId, 'listeMember' => $listeMember]);
    }

    /**
     * @Route("/private-group-invite", name="member_private_group_invite", methods="GET|POST")
     */
    public function memberPrivateGroupInvite(Request $request, PrivateGroupMemberRepository $privateGroupMemberRepository)
    {
        $privateGroupId = intval($request->request->get('privateGroupId') ?? null);
        $memberId = intval($request->request->get('memberId') ?? null);
        $privateGroupMemberRole = $request->request->get('privateGroupMemberRole') ?? "GROUP_READER";

        if ($privateGroupId != null){

            $em = $this->getDoctrine()->getManager();

            $privateGroup = $em->getRepository(PrivateGroup::class)
                                ->find($privateGroupId);
            $privateGroupAdmin = $privateGroupMemberRepository->findOneBy(["privateGroup" => $privateGroup, "member" => $this->getUser()]);

            if ($privateGroupAdmin->getRole() == "GROUP_ADMIN"){
                if ($memberId != null && $memberId != $this->getUser()->getId()){

                    $member = $em->getRepository(Member::class)
                                ->find($memberId);

                    if ($privateGroupMember = $privateGroupMemberRepository->findOneBy(["privateGroup" => $privateGroup, "member" => $member])){
                        if ($privateGroupMember->getJoinStatus() == "JOIN"){$privateGroupMember->setJoinStatus("");}
                    }else{
                        $privateGroupMember = new PrivateGroupMember;
                        $privateGroupMember->setPrivateGroup($privateGroup);
                        $privateGroupMember->setMember($member);
                        $privateGroupMember->setJoinStatus("INVITED");
                     }

                    $privateGroupMember->setRole($privateGroupMemberRole);

                    $em->persist($privateGroupMember);
                    $em->flush();

                }
                $listeMember = $privateGroupMemberRepository->userOutPrivateGroup($privateGroup);

                return $this->render('member_private_group/list-member.html.twig', ['privateGroupId' => $privateGroupId, 'listeMember' => $listeMember]);
            }
        }
        return $this->redirectToRoute('member_zone');
    }

    /**
     * @Route("/private-group-accept-invite", name="member_private_group_accept_invite", methods="GET|POST")
     */
    public function memberPrivateGroupAcceptInvite(Request $request, PrivateGroupMemberRepository $privateGroupMemberRepository)
    {
        $privateGroupId = intval($request->request->get('privateGroupId') ?? null);
        
        if ($privateGroupId != null){

            $em = $this->getDoctrine()->getManager();

            $privateGroup = $em->getRepository(PrivateGroup::class)
                                ->find($privateGroupId);
            $privateGroupInvited = $privateGroupMemberRepository->findOneBy(["privateGroup" => $privateGroup, "member" => $this->getUser()]);

            if ($privateGroupInvited->getJoinStatus() == "INVITED"){
                $privateGroupInvited->setJoinStatus("");

                $em->persist($privateGroupInvited);
                $em->flush();
            }            
        }
        return $this->redirectToRoute('member_zone');
    }

    /**
     * @Route("/private-group-join", name="member_private_group_join", methods="GET|POST")
     */
    public function memberPrivateGroupJoin(Request $request, PrivateGroupMemberRepository $privateGroupMemberRepository)
    {
        $privateGroupId = intval($request->request->get('privateGroupId') ?? null);
        
        if ($privateGroupId != null){

            $em = $this->getDoctrine()->getManager();

            $privateGroup = $em->getRepository(PrivateGroup::class)
                                ->find($privateGroupId);
            $privateGroupMember = $privateGroupMemberRepository->findOneBy(["privateGroup" => $privateGroup, "member" => $this->getUser()]);

            if ($privateGroupMember){
                if ($privateGroupMember->getJoinStatus() == "INVITED"){$privateGroupMember->setJoinStatus("");}
            }else{
                $privateGroupMember = new PrivateGroupMember;
                $privateGroupMember->setPrivateGroup($privateGroup);
                $privateGroupMember->setMember($this->getUser());
                $privateGroupMember->setRole("GROUP_READER");
                $privateGroupMember->setJoinStatus("JOIN");
            }

            $em->persist($privateGroupMember);
            $em->flush();
        }

        $listeGroup = $privateGroupMemberRepository->privateGroupWithOutUser($this->getUser());

        return $this->render('member_private_group/list-group.html.twig', ['listeGroup' => $listeGroup]);
    }

    /**
     * @Route("/private-group-accept-join", name="member_private_group_accept_join", methods="GET|POST")
     */
    public function memberPrivateGroupAcceptJoin(Request $request, PrivateGroupMemberRepository $privateGroupMemberRepository)
    { 
        $privateGroupId = intval($request->request->get('privateGroupId') ?? null);
        $memberId = intval($request->request->get('memberId') ?? null);
        $privateGroupMemberRole = $request->request->get('privateGroupMemberRole') ?? "GROUP_READER";

        if ($privateGroupId != null){

            $em = $this->getDoctrine()->getManager();

            $privateGroup = $em->getRepository(PrivateGroup::class)
                                ->find($privateGroupId);
            $privateGroupAdmin = $privateGroupMemberRepository->findOneBy(["privateGroup" => $privateGroup, "member" => $this->getUser()]);

            if ($privateGroupAdmin->getRole() == "GROUP_ADMIN"){
                if ($memberId != null){

                    $member = $em->getRepository(Member::class)
                                ->find($memberId);
                    if ($privateGroupMember = $privateGroupMemberRepository->findOneBy(["privateGroup" => $privateGroup, "member" => $member])){
                        if ($privateGroupMember->getJoinStatus() == "JOIN"){
                            $privateGroupMember->setJoinStatus("");
                            $privateGroupMember->setRole($privateGroupMemberRole);
                            $em->persist($privateGroupMember);
                            $em->flush();
                        }
                    }
                }
                $listeMember = $privateGroupMemberRepository->userJoinPrivateGroup($privateGroup);

                return $this->render('member_private_group/list-member.html.twig', ['privateGroupId' => $privateGroupId, 'listeMember' => $listeMember]);
            }
        }
        return $this->redirectToRoute('member_zone');
    }
}
