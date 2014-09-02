<?php
/**
 * Author: Petr
 * Date Created: 02.09.14 13:46
 */

namespace Petr\DirectApiBundle\Service;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;

class DirectService {

    protected $kernel;

    public function __construct(Kernel $kernel) {
        $this->kernel = $kernel;
    }

    public function initialize() {
        // загрузка конфига для директа
        $path = $this->kernel->locateResource("@PetrDirectApiBundle/Resources/config/yandexDirect.yml");
        if (!is_string($path)) {
            throw new Exception("Type of $path must be string.");
        }
        $directConfig = Yaml::parse(file_get_contents($path));

        return $directConfig;
    }

} 