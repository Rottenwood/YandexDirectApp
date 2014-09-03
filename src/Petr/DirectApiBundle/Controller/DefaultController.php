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

        $data = array();

        $campaigns = array();
        $dateFrom = "";
        $dateTo = "";

//        $dateFrom = "2014-01-01-";
//        $dateTo = "2015-01-01";
        //        $this->get('direct')->api("GetCampaignsList");

        $campaignStat = $directService->getCampaignStat($campaigns, $dateFrom, $dateTo);

        //        $this->get('direct')->getCampaignStat($campaigns);
        //
        //        $campaignIds = array("CampaignIDS" => $campaigns);
        //        $this->get('direct')->api("GetCampaignsParams", $campaignIds);

        //        $allCampaignsIds = $directService->getAllCampaignsIds();

        var_dump($campaignStat);

        return $this->render('PetrDirectApiBundle:Default:index.html.twig', $data);
    }
}
