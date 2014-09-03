<?php
/**
 * Author: Petr
 * Date Created: 02.09.14 13:46
 */

namespace Petr\DirectApiBundle\Service;

use Doctrine\ORM\EntityManager;
use Petr\DirectApiBundle\Entity\Banner;
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
    protected $bannerRepository;

    public function __construct(EntityManager $em, Kernel $kernel, Browser $buzz) {
        $this->em = $em;
        $this->campaignRepository = $this->em->getRepository('PetrDirectApiBundle:Camp');
        $this->bannerRepository = $this->em->getRepository('PetrDirectApiBundle:Banner');
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
        $campaigns = $this->campaignRepository->findAll();

        return $campaigns;
    }

    /**
     * Запрос объявлений из базы данных
     * @param $campaigns
     * @return array|\Petr\DirectApiBundle\Entity\Banner[]
     */
    public function bannersLoad($campaigns) {
        $bannersIds = $this->bannerRepository->findAllBannersByCampaigns($campaigns);

        return $bannersIds;
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

        // отправка запроса к API
        $response = $this->buzz->post($this->directConfig["sandbox_url"], array(), json_encode($contents));

        echo $this->buzz->getLastRequest() . "<br>";
        echo "*****************<br>";

        $responseJson = $response->getContent();
        $responseArray = json_decode($responseJson, true);

        // обработка ошибок обращения к API
        if (array_key_exists('error_str', $responseArray)) {
            throw new Exception($responseArray['error_str'] . ": " . $responseArray['error_detail']);
        }

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

    /**
     * Получение стратегии по кампаниям из базы данных
     * @return array
     */
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

    //    /**
    //     * Запрос к API на получение всех объявлений для выбранных кампаний
    //     * @param $campaignIds
    //     * @return mixed
    //     */
    //    public function getAllBannersByCampaign($campaignIds) {
    //        $params = array(
    //            'CampaignIDS' => $campaignIds,
    //        );
    //
    //        $banners = $this->api("GetBanners", $params);
    //
    //        // обработка результатов запроса
    //        if (array_key_exists('data', $banners)) {
    //            $banners = $banners["data"];
    //        }
    //
    //        return $banners;
    //    }

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

        // интервал в днях между временем начала и окончания
        $timeInterval = date_diff(date_create($dateFrom), date_create($dateTo))->days;

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

        // проверка на соответствие статистики (эффективность кампании)
        $checkEffectiveness = $this->checkEffectiveness($campaignStat);

        // результирующее сообщение
        if (count($checkEffectiveness) > 0) {
            $checkEffectivenessString = implode(', ', $checkEffectiveness);
            $messageCampaign = "Были остановлены следующие кампании: $checkEffectivenessString";
        } else {
            $messageCampaign = "Все кампании удовлетворяют требованиям выбранной стратегии.";
        }

        // проверка объявлений
        $bannersStat = $this->getAllBannersFromCampaigns($campaigns, $dateFrom, $dateTo);
        $checkEffectivenessBanner = $this->checkEffectivenessBanners($campaigns, $bannersStat, $timeInterval);

        // результирующее сообщение
        if ($checkEffectivenessBanner && count($checkEffectivenessBanner) != 0) {
            $checkEffectivenessBannerString = implode(', ', $checkEffectivenessBanner);
            $messageBanner = "Были остановлены следующие объявления: $checkEffectivenessBannerString";
        } else {
            $messageBanner = "Все объявления удовлетворяют требованиям выбранной стратегии.";
        }

        // сообщение для вида (view)
        $message = array(
            'campaign' => $messageCampaign,
            'banner'   => $messageBanner,
        );

        return $message;
    }

    /**
     * Запрос к Яндекс.директ API о статистике по объявлениям
     * для одной кампании
     * @param $campaignId
     * @param $dateFrom
     * @param $dateTo
     * @return mixed
     */
    public function getBannerStat($campaignId, $dateFrom, $dateTo) {
        $params = array(
            'CampaignID' => $campaignId,
            'StartDate'  => $dateFrom,
            'EndDate'    => $dateTo,
        );

        $bannerStat = $this->api("GetBannersStat", $params);

        // обработка результатов запроса
        if (array_key_exists('data', $bannerStat)) {
            $bannerStat = $bannerStat["data"]["Stat"];
        } else {
            return false;
        }

        return $bannerStat;
    }

    /**
     * Запрос к API на получение всех объявлений для кампании
     * @param array $campaigns
     * @param       $dateFrom
     * @param       $dateTo
     * @return array
     */
    public function getAllBannersFromCampaigns($campaigns, $dateFrom, $dateTo) {
        $allBannersStat = array();

        foreach ($campaigns as $campaignId) {
            // получение статистики по всем объявлениям кампании
            $bannerStat = $this->getBannerStat($campaignId, $dateFrom, $dateTo);

            // только кампании с объявлениями
            if ($bannerStat) {
                $allBannersStat[$campaignId] = $bannerStat;
            }
        }

        return $allBannersStat;
    }

    /**
     * Остановка рекламной кампании
     * @param $campaignId
     * @return mixed
     */
    public function stopCampaign($campaignId) {
        $params = array(
            'CampaignID' => $campaignId,
        );

        $result = $this->api("StopCampaign", $params);

        return $result;
    }

    /**
     * Остановка показа объявлений
     * @param array $campaignId
     * @param       $bannerIds
     * @return mixed
     */
    public function stopBanner($campaignId, $bannerIds) {
        $params = array(
            'CampaignID' => $campaignId,
            'BannerIDS'  => $bannerIds,
        );

        $result = $this->api("StopBanners", $params);

        return $result;
    }

    public function checkEffectiveness($campaignsRemote) {
        $pausedCampaigns = array();
        $campaignsStrategy = $this->campaignsLoad();

        // проверка статистики кампаний
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
                    throw new Exception("Ошибка при постановке кампании $campaignId на паузу");
                }

                $pausedCampaigns[] = $campaignId;
            }
        }

        return $pausedCampaigns;
    }

    // оценка эффективности объявлений из БД
    public function checkEffectivenessBanners($campaigns, $bannersStat, $timeInterval) {
        $pausedBanners = array();
        $bannersStrategy = $this->bannersLoad($campaigns);

        // приведение интервала в пригодный для умножения вид
        $timeInterval++;

        // если в БД нет объявлений
        if (!$bannersStrategy) {
            return false;
        }

        // проверка статистики объявлений
        foreach ($bannersStat as $bannersStatCampaignId => $bannersStatCampaign) {
            foreach ($bannersStatCampaign as $banner) {
                $bannerId = $banner["BannerID"];
                $bannerClick = $banner["ClicksContext"];
                $bannerPrice = $banner["SumContext"];

                // поиск параметров объявлений (из БД)
                $strategyObj = null;
                /** @var Banner $strategy */
                foreach ($bannersStrategy as $strategy) {
                    if ($bannerId == $strategy->getBannerId()) {
                        $strategyObj = $strategy;
                        break;
                    }
                }

                // если в БД не найдено ни одного подходящего объявления
                if (!$strategyObj) {
                    break;
                }

                // параметры стратегии из БД помноженные на количество дней в запросе
                $bannerDailyClicks = $strategyObj->getDailyclicks() * $timeInterval;
                $bannerDailyCosts = $strategyObj->getDailycosts() * $timeInterval;

                var_dump($bannerClick);
                var_dump($bannerDailyClicks);
                var_dump($bannerPrice);
                var_dump($bannerDailyCosts);

                // сверка кликов и затрат
                if ($bannerClick < $bannerDailyClicks
                    || $bannerPrice > $bannerDailyCosts
                ) {
                    $result = $this->stopBanner($bannersStatCampaignId, array($bannerId));

                    if ($result["data"] != 1) {
                        throw new Exception("Ошибка при постановке объявления $bannerId на паузу");
                    }

                    $pausedBanners[] = $bannerId;
                }
            }
        }

        if (count($pausedBanners) == 0) {
            $pausedBanners = false;
        }

        return $pausedBanners;
    }

}