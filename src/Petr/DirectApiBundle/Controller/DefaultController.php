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
        $directService = $this->get('direct');

        // параметры для запроса статистики
        $campaigns = $directService->getAllLocalCampaignsIds();
        $dateFrom = "2014-01-01";
        $dateTo = "2015-01-01";

//        $campaignStat = $directService->getCampaignStat($campaigns, $dateFrom, $dateTo);
//        $campaignStat = $directService->getCampaignStat(array(), $dateFrom, '');
//        var_dump($campaignStat);

//        var_dump($directService->checkEffectiveness());

        $data = array();
        return $this->render('PetrDirectApiBundle:Default:index.html.twig', $data);
    }
}
