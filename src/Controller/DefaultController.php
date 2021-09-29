<?php

namespace App\Controller;

use App\Entity\FfvbCSV;
use App\Entity\FfvbLink;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
     * @Route("team/select", name="selectTeam", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function selectTeam(Request $request): Response
    {
        $ffvbLinkSubmitted = $request->request->get('ffvbLink');

        if($ffvbLinkSubmitted === '') {
            return $this->render('homepage.html.twig');
        }

        $ffvbLink = new FfvbLink($ffvbLinkSubmitted);
        $ffvbCSV = new FfvbCSV($ffvbLink);
        $ffvbCSV->setFFVBCalendar();

        $session = $request->getSession();
        $session->set('ffvbCSV', $ffvbCSV);

        return $this->render('select_team.html.twig', [
            'teams' => $ffvbCSV->getAllTeams(),
        ]);
    }

    /**
     * @Route("calendar/download", name="downloadGcal", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function downloadGcal(Request $request): Response
    {
        $teamSelected = $request->request->get('team');

        $session = $request->getSession();

        /** @var FfvbCSV $ffvbCSV */
        $ffvbCSV = $session->get('ffvbCSV');
        $ffvbCSV->keepGamesByTeam($teamSelected);
        $ffvbCSV->convertFFVBCsvToGcal();

        $response = new Response($ffvbCSV->getGcalCsv());
        $response->headers->set('Content-Type', 'text/csv');

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $teamSelected.'.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}