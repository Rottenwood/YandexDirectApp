<?php
/**
 * Author: Petr
 * Date Created: 02.09.14 13:46
 */

namespace Petr\DirectApiBundle\Service;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;
use Buzz\Browser;

class DirectService {

    protected $kernel;
    protected $directConfig;
    protected $buzz;

    public function __construct(Kernel $kernel, Browser $buzz) {
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
     * @param $method
     * @param $params
     * @return bool
     */
    public function api($method, $params = '') {
        $contents = array(
            'method' => $method,
            'params' => $params,
            'token'  => $this->directConfig["usertoken"],
            'locale' => $this->directConfig["locale"],
        );

        $response = $this->buzz->post($this->directConfig["sandbox_url"], array(), json_encode($contents));

        echo $this->buzz->getLastRequest() . "<br>";
        echo "*****************<br>";
        $responseJson = $response->getContent();
        $responseArray = json_decode($responseJson, TRUE);
        $responseData = $responseArray["data"];

        foreach ($responseData as $item => $data) {
            $campaignId = $data["CampaignID"];
            $responseData[$campaignId] = $responseData[$item];
            unset($responseData[$item]);
        }

        var_dump($responseData);

        $data = array();
        $data['config'] = $this->directConfig;


        return true;

    }

} 