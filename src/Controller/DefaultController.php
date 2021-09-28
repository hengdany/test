<?php

namespace App\Controller;

use App\Entity\FfvbCSV;
use App\Entity\FfvbLink;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function homepage()
    {
        return $this->render('homepage.html.twig');
    }

    /**
     * @Route("team/select", name="selectTeam")
     */
    public function selectTeam(Request $request)
    {
        $ffvbLinkSubmitted = $request->request->get('ffvbLink');

        if($ffvbLinkSubmitted === '') {
            return $this->render('homepage.html.twig');
        }

        $ffvbLink = new FfvbLink($ffvbLinkSubmitted);
        $ffvbCSV = new FfvbCSV($ffvbLink);
        $ffvbCSV->setFFVBCalendar();

        $session = $request->getSession();
        $session->set('ffvbLink', $ffvbLink);

        return $this->render('select_team.html.twig', [
            'teams' => $ffvbCSV->getAllTeams(),
        ]);
    }
}