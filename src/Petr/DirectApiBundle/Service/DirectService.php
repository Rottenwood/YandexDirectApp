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

class DirectService {

    protected $em;
    protected $kernel;
    protected $directConfig;
    protected $buzz;

    public function __construct(EntityManager $em, Kernel $kernel, Browser $buzz) {
        $this->em = $em;
        $this->kernel = $kernel;
        $this->directConfig = $this->configLoad();
        $this->buzz = $buzz;
    }

    /**
     * Загрузка конфига для Яндекс.директа
     * @return array
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    public function configLoad() {
        $path = $this->kernel->locateResource("@PetrDirectApiBundle/Resources/config/yandexDirect.yml");
        if (!is_string($path)) {
            throw new Exception("Type of $path must be string.");
        }
        $directConfig = Yaml::parse(file_get_contents($path));

        return $directConfig;
    }

    /**
     * Запрос к API Яндекс.директа
     * @param        $method
     * @param string $params
     * @internal param array $param
     * @return bool
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
     * Список ID всех кампаний
     * @return array
     */
    public function getAllCampaignsIds() {
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
     * @return bool
     */
    public function getCampaignStat($campaigns = array(), $dateFrom = '', $dateTo = '') {

        // если не указаны ID кампаний
        if (!$campaigns) {
            $campaigns = $this->getAllCampaignsIds();
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
        )) {
            $error = "Неверно задана дата: Y-m-d";
            throw new Exception($error);
        }

        $params = array(
            'CampaignIDS' => $campaigns,
            'StartDate'   => $dateFrom,
            'EndDate'     => $dateTo,
        );

        $campaignStat = $this->api("GetSummaryStat", $params);

        if (array_key_exists('data', $campaignStat)) {
            $campaignStat = $campaignStat["data"];
        }

        return $campaignStat;
    }

} 