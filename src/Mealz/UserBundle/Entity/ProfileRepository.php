<?php

namespace App\Mealz\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class ProfileRepository.
 */
class ProfileRepository extends EntityRepository
{
    /**
     * find all profiles except the one with the given username.
     *
     * @param array $usernames
     *
     * @return array
     */
    public function findAllExcept($usernames)
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->addSelect('r');
        $queryBuilder->leftJoin('p.roles', 'r');
        $queryBuilder->where($queryBuilder->expr()->notIn('p.username', ':usernames'));
        $queryBuilder->setParameter(':usernames', $usernames);

        return $queryBuilder->getQuery()->getResult();
    }
}
