<?php

namespace App\Mealz\UserBundle\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;

class RoleRepository extends EntityRepository
{
    /**
     * @param string[] $sids
     *
     * @return Role[]
     */
    public function findBySID(array $sids): array
    {
        $queryBuilder = $this->createQueryBuilder('r');
        $queryBuilder->where($queryBuilder->expr()->in('r.sid', ':sids'));
        $queryBuilder->setParameter('sids', $sids, Connection::PARAM_STR_ARRAY);

        $roles = $queryBuilder->getQuery()->getResult();

        return is_array($roles) ? $roles : [];
    }
}
