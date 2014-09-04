<?php

namespace Petr\DirectApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController
 * @package Petr\DirectApiBundle\Controller
 */
class DefaultController extends Controller {

    /**
     * Главная страница
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction() {
        $data = array();
        $directService = $this->get('direct');

        // параметры для запроса статистики
        $campaigns = $directService->getAllLocalCampaignsIds();
        $dateFrom = "2014-09-04";
        $dateTo = "2014-09-04";

//        $campaignStat = $directService->getCampaignStat();
        $campaignStat = $directService->getCampaignStat($campaigns, $dateFrom, $dateTo);
//        $campaignStat = $directService->getCampaignStat(array(67807));
        $data["checkResult"] = $campaignStat;

        return $this->render('PetrDirectApiBundle:Default:index.html.twig', $data);
    }
}
