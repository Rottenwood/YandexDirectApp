<?php
/**
 * Author: Petr
 * Date Created: 02.09.14 13:46
 */

namespace Petr\DirectApiBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;
use Buzz\Browser;
use Petr\DirectApiBundle\Entity\Camp;

/**
 * Сервис для общения с Яндекс.директ API
 * @package Petr\DirectApiBundle\Service
 */
class DirectService {

    protected $em;
    protected $kernel;
    protected $directConfig;
    protected $buzz;
    protected $campaignRepository;

    public function __construct(EntityManager $em, Kernel $kernel, Browser $buzz) {
        $this->em = $em;
        $this->campaignRepository = $this->em->getRepository('PetrDirectApiBundle:Camp');
        $this->kernel = $kernel;
        $this->directConfig = $this->configLoad();
        $this->buzz = $buzz;
    }

    /**
     * Загрузка конфига для Яндекс.директа
     * @return array
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    private function configLoad() {
        $path = $this->kernel->locateResource("@PetrDirectApiBundle/Resources/config/yandexDirect.yml");
        if (!is_string($path)) {
            throw new Exception("Type of $path must be string.");
        }
        $directConfig = Yaml::parse(file_get_contents($path));

        return $directConfig;
    }

    /**
     * Запрос кампаний из базы данных
     * @return array|\Petr\DirectApiBundle\Entity\Camp[]
     */
    public function campaignsLoad() {
        $campaignsIds = $this->campaignRepository->findAll();

        return $campaignsIds;
    }

    /**
     * Запрос к API Яндекс.директа
     * @param        $method
     * @param string $params
     * @internal param array $param
     * @return mixed
     */
    public function api($method, $params = '') {
        $contents = array(
            'method' => $method,
            'token'  => $this->directConfig["usertoken"],
            'locale' => $this->directConfig["locale"],
        );

        if ($params) {
            $contents["param"] = $params;
        }

        $response = $this->buzz->post($this->directConfig["sandbox_url"], array(), json_encode($contents));

        echo $this->buzz->getLastRequest() . "<br>";
        echo "*****************<br>";
        $responseJson = $response->getContent();
        $responseArray = json_decode($responseJson, true);

        return $responseArray;
    }

    /**
     * Список ID всех кампаний из базы данных
     * @return array
     */
    public function getAllLocalCampaignsIds() {
        $allLocalCampaigns = $this->campaignsLoad();
        $allLocalCampaignsIds = array();

        /** @var Camp $campaign */
        foreach ($allLocalCampaigns as $campaign) {
            $allLocalCampaignsIds[] = $campaign->getCampaignId();
        }

        return $allLocalCampaignsIds;
    }

    public function getStrategyStatLocal() {
        $allLocalCampaigns = $this->campaignsLoad();
        $allLocalCampaignsStrategy = array();

        /** @var Camp $campaign */
        foreach ($allLocalCampaigns as $campaign) {
            $allLocalCampaignsStrategy[] = array(
                'campaignId'   => $campaign->getCampaignId(),
                'daylyClicks'  => $campaign->getDailyclicks(),
                'daylyCosts'   => $campaign->getDailycosts(),
                'weeklyClicks' => $campaign->getWeeklyclicks(),
                'weeklyCosts'  => $campaign->getWeeklycosts(),
            );
        }

        return $allLocalCampaignsStrategy;
    }

    /**
     * Список ID всех кампаний из директа
     * @return array
     */
    public function apiAllCampaignsIds() {
        $allCampaigns = $this->api("GetCampaignsList");
        $allCampaignsIds = array();

        foreach ($allCampaigns["data"] as $campaign) {
            $allCampaignsIds[] = $campaign["CampaignID"];
        }

        return $allCampaignsIds;
    }

    /**
     * Статистика по кампаниям за времянной промежуток
     * @param        $campaigns array
     * @param string $dateFrom
     * @param string $dateTo
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     * @return mixed
     */
    public function getCampaignStat($campaigns = array(), $dateFrom = '', $dateTo = '') {

        // если не указаны ID кампаний
        if (!$campaigns) {
            $campaigns = $this->apiAllCampaignsIds();
        }

        // дефолтное значение даты
        if (!$dateFrom) {
            $dateFrom = date("Y-m-d");
        }
        if (!$dateTo) {
            $dateTo = date("Y-m-d");
        }

        // валидация даты
        $format = "Y-m-d";
        if (!(\DateTime::createFromFormat($format, $dateFrom) == true
            && \DateTime::createFromFormat($format, $dateTo) == true
        )
        ) {
            $error = "Неверно задана дата: Y-m-d";
            throw new Exception($error);
        }

        // параметры для запроса
        $params = array(
            'CampaignIDS' => $campaigns,
            'StartDate'   => $dateFrom,
            'EndDate'     => $dateTo,
        );

        // запрос к API
        $campaignStat = $this->api("GetSummaryStat", $params);

        // обработка результатов запроса
        if (array_key_exists('data', $campaignStat)) {
            $campaignStat = $campaignStat["data"];
        }

        return $campaignStat;
    }

    public function stopCampaign($campaignId) {
        $params = array(
            'CampaignID' => $campaignId,
        );

        $result = $this->api("StopCampaign", $params);

        return $result;
    }


    public function checkEffectiveness() {
        $campaignsStrategy = $this->campaignsLoad();
        $campaignsRemote = $this->getCampaignStat(array(), '2014-09-01', '2014-09-03'); // удалить после отладки

        foreach ($campaignsRemote as $campaign) {
            $campaignId = $campaign["CampaignID"];
            $campaignClick = $campaign["ClicksContext"];
            $campaignPrice = $campaign["SumContext"];

            // поиск параметров стратегии (из БД) для данной кампании
            $strategyObj = null;
            foreach ($campaignsStrategy as $strategy) {
                if ($campaignId == $strategy->getCampaignId()) {
                    $strategyObj = $strategy;
                    break;
                }
            }

            // сверка кликов и затрат
            if ($campaignClick < $strategyObj->getDailyclicks()
                || $campaignPrice > $strategyObj->getDailycosts()
            ) {
                $result = $this->stopCampaign($campaignId);

                if ($result["data"] != 1) {
                    throw new Exception("Ошибка при постановке кампании на паузу");
                }

                return false;
            }
        }

        return true;
    }

}