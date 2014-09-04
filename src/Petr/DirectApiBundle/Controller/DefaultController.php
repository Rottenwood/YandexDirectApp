<?php

namespace Petr\DirectApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController
 * @package Petr\DirectApiBundle\Controller
 */
class DefaultController extends Controller {

    /**
     * Главная точка входа для работы с приложением
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction() {
        $data = array();
        $directService = $this->get('direct');

        // параметры для запроса статистики
        $campaigns = $directService->getAllLocalCampaignsIds();
        $dateFrom = "2014-08-29";
        $dateTo = "2014-09-04";

        // для проверки: запуск функции без передачи значений
        // $campaignCheckResult = $directService->checkEffectiveness();
        $campaignCheckResult = $directService->checkEffectiveness($campaigns, $dateFrom, $dateTo);

        $data["checkResult"] = $campaignCheckResult;

        return $this->render('PetrDirectApiBundle:Default:index.html.twig', $data);
    }
}
