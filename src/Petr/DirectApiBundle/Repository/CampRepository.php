<?php

namespace Petr\DirectApiBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Репозиторий запросов к таблице "Кампании" (campaigns)
 */
class CampRepository extends EntityRepository {
    public function findAllCampaigns() {
        $query = $this->getEntityManager()
            ->createQuery('SELECT c FROM PetrDirectApiBundle:Camp c');
        $result = $query->getResult();

        return $result;
    }
}
