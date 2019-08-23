<?php

namespace Mealz\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class ProfileRepository
 * @package Mealz\UserBundle\Entity
 */
class ProfileRepository extends EntityRepository
{
    /**
     * find all profiles except the one with the given username
     * @param array $usernames
     * @return array
     */
    public function findAllExcept($usernames)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->addSelect('r');
        $qb->leftJoin('p.roles', 'r');
        $qb->where($qb->expr()->notIn('p.username', ':usernames'));
        $qb->setParameter(':usernames', $usernames);

        return $qb->getQuery()->getResult();
    }

    /**
     * find the profile  with the given username
     * @param string $usernames
     * @return profile
     */
    public function findByUsername($username)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->addSelect('r');
        $qb->leftJoin('p.roles', 'r');
        $qb->where($qb->expr()->in('p.username', ':username'));
        $qb->setParameter(':username', $username);

        return $qb->getQuery()->getResult();
    }
}
