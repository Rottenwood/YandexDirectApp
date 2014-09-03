<?php

namespace Petr\DirectApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Banner
 * @ORM\Table(name="banners")
 * @ORM\Entity(repositoryClass="Petr\DirectApiBundle\Repository\BannerRepository")
 */
class Banner {

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     * @ORM\Column(name="banner", type="integer", unique=true)
     */
    private $bannerId;

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
     * @ORM\ManyToOne(targetEntity="Camp")
     * @ORM\JoinColumn(name="camp_id", referencedColumnName="id")
     */

    private $campId;

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
     * @return Banner
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
     * @return Banner
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
     * @return Banner
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
     * @return Banner
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
     * Set campId
     * @param integer $campId
     * @return Banner
     */
    public function setCampId($campId) {
        $this->campId = $campId;

        return $this;
    }

    /**
     * Get campId
     * @return integer
     */
    public function getCampId() {
        return $this->campId;
    }

    /**
     * @param int $bannerId
     */
    public function setBannerId($bannerId) {
        $this->bannerId = $bannerId;
    }

    /**
     * @return int
     */
    public function getBannerId() {
        return $this->bannerId;
    }


}
