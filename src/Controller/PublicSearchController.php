<?php

namespace App\Controller;


use App\Entity\Info;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PublicSearchController extends AbstractController
{
    /**
     * @Route("/search", name="public_search")
     */
    public function index(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Info::class);
        $search = $request->request->get('search');

        $result = $repository->research("%".$search."%");
        return $this->render('public_search/resultat.html.twig', [
            'result' => $result,
        ]);
    }

    /**
     * @Route("/multisearch", name="public_multisearch")
     */
    public function multiSearch(Request $request)
    {
        $dateStart= new \datetime( $request->request->get('dateStart'));
        $dateEnd=  new \datetime($request->request->get('dateEnd'));
        dump($dateEnd);
        $repository = $this->getDoctrine()->getRepository(Info::class);

        $result = $repository->multiResearch($dateStart,$dateEnd);
        return $this->render('public_search/resultat.html.twig', [
            'result' => $result,
        ]);
    }
}
