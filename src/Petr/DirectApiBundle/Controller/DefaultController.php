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

        $this->get('direct')->api("GetCampaignsList");

        return $this->render('PetrDirectApiBundle:Default:index.html.twig', $data);
    }
}
