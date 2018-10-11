<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Upload;

class ServiceController extends AbstractController
{
    /**
     * @Route("/service", name="service")
     */
    public function index(Upload $Upload)
    {
        //$Upload->directory('/1');

        return $this->render('service/index.html.twig', [
            'controller_name' => 'ServiceController',
        ]);
    }
}
