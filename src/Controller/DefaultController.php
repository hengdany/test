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
     * @Route("team/select", name="selectTeam")
     * @param Request $request
     * @return Response
     */
    public function selectTeam(Request $request): Response
    {
        $ffvbLinkSubmitted = $request->request->get('ffvbLink');

        if($ffvbLinkSubmitted === '') {
            return $this->render('homepage.html.twig');
        }

        $ffvbLink = FfvbLink::createFromLink($ffvbLinkSubmitted);
        $ffvbCSV = new FfvbCSV($ffvbLink);
        $ffvbCSV->setFFVBCalendar();

        $session = $request->getSession();
        $session->set('ffvbCSV', $ffvbCSV);

        return $this->render('select_team.html.twig', [
            'teams'  => $ffvbCSV->getAllTeams(),
            'saison' => urlencode($ffvbLink->cal_saison),
            'codent' => $ffvbLink->cal_codent,
            'poule'  => urlencode($ffvbLink->cal_codpoule)
        ]);
    }

    /**
     * @Route("calendar/download", name="downloadGcal")
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

        $response = new Response($ffvbCSV->getIcs());
        $response->headers->set('Content-Type', 'text/calendar');

        $teamSelected = preg_replace(
            '#^.*\.#', '', $teamSelected
        );

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $teamSelected.'.ics',
            'calendrier_volley.ics'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * Download with team 
     * 
     * @Route("quick/download", name="quickDownload")
     * @param Request $request
     * @return Response
     */
    public function quickDownload(Request $request): Reponse
    {
        $ffvbLink = new FfvbLink();
        $ffvbLink->cal_saison   = urldecode($request->request->get('saison'));
        $ffvbLink->cal_codent   = $request->request->get('codent');
        $ffvbLink->cal_codpoule = urldecode($request->request->get('poule'));

        $team = $request->request->get('team');

        $ffvbCSV = new FfvbCSV($ffvbLink);
        $ffvbCSV->setFFVBCalendar();
        $request->getSession()->set('ffvbCSV', $ffvbCSV);
        $this->downloadGcal($request);
    }
}
