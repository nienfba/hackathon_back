<?php

namespace App\Controller;

use App\Repository\InfoRepository;
use App\Repository\MemberRepository;
use App\Repository\MessageRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin")
 */
class AdminZoneController extends AbstractController
{
    /**
     * @Route("/", name="admin_zone")
     */
    public function index(MemberRepository $memberRepository, InfoRepository $infoRepository, MessageRepository $messageRepository)
    {
        return $this->render('admin_zone/index.html.twig', [
            'controller_name' => 'AdminZoneController',
            'members' => $memberRepository->findAll(),
            'infos' => $infoRepository->findAll(),
            'messages' => $messageRepository->findAll(),
        ]);
    }
}
