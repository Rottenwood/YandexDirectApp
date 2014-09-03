<?php

namespace Petr\DirectApiBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Репозиторий запросов к таблице "Объявления" (banners)
 */
class BannerRepository extends EntityRepository {

    /**
     * Запрос: найти все объявления выбранных кампаний
     * @param $campaigns
     * @return array
     */
    public function findAllBannersByCampaigns($campaigns) {
        $query = $this->getEntityManager()
            ->createQuery('SELECT b FROM PetrDirectApiBundle:Banner b LEFT JOIN b.campId c WHERE c.campaignId IN ( :campaigns )');
        $query->setParameter('campaigns', $campaigns);
        $result = $query->getResult();

        return $result;
    }
}
