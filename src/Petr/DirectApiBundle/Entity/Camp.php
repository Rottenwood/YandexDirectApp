<?php

namespace Petr\DirectApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Camp
 * @ORM\Table(name="campaigns")
 * @ORM\Entity(repositoryClass="Petr\DirectApiBundle\Repository\CampRepository")
 */
class Camp {

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     * @ORM\Column(name="campaign", type="integer", unique=true)
     */
    private $campaignId;

    /**
     * @var integer
     * @ORM\Column(name="dailyclicks", type="integer")
     */
    private $dailyclicks;

    /**
     * @var integer
     * @ORM\Column(name="dailycosts", type="integer")
     */
    private $dailycosts;

    /**
     * @var integer
     * @ORM\Column(name="weeklyclicks", type="integer")
     */
    private $weeklyclicks;

    /**
     * @var integer
     * @ORM\Column(name="weeklycosts", type="integer")
     */
    private $weeklycosts;


    /**
     * Get id
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set dailyclicks
     * @param integer $dailyclicks
     * @return Camps
     */
    public function setDailyclicks($dailyclicks) {
        $this->dailyclicks = $dailyclicks;

        return $this;
    }

    /**
     * Get dailyclicks
     * @return integer
     */
    public function getDailyclicks() {
        return $this->dailyclicks;
    }

    /**
     * Set dailycosts
     * @param integer $dailycosts
     * @return Camps
     */
    public function setDailycosts($dailycosts) {
        $this->dailycosts = $dailycosts;

        return $this;
    }

    /**
     * Get dailycosts
     * @return integer
     */
    public function getDailycosts() {
        return $this->dailycosts;
    }

    /**
     * Set weeklyclicks
     * @param integer $weeklyclicks
     * @return Camps
     */
    public function setWeeklyclicks($weeklyclicks) {
        $this->weeklyclicks = $weeklyclicks;

        return $this;
    }

    /**
     * Get weeklyclicks
     * @return integer
     */
    public function getWeeklyclicks() {
        return $this->weeklyclicks;
    }

    /**
     * Set weeklycosts
     * @param integer $weeklycosts
     * @return Camps
     */
    public function setWeeklycosts($weeklycosts) {
        $this->weeklycosts = $weeklycosts;

        return $this;
    }

    /**
     * Get weeklycosts
     * @return integer
     */
    public function getWeeklycosts() {
        return $this->weeklycosts;
    }

    /**
     * @param int $campaignId
     */
    public function setCampaignId($campaignId) {
        $this->campaignId = $campaignId;
    }

    /**
     * @return int
     */
    public function getCampaignId() {
        return $this->campaignId;
    }


}
