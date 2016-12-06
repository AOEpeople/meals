<?php

namespace Mealz\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProfileRepository extends EntityRepository
{
    /**
     * @param array $usernames
     * @return array
     */
    public function findAllExcept($usernames)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where($qb->expr()->notIn('p.username', ':usernames'));
        $qb->setParameter(':usernames', $usernames);
        return $qb->getQuery()->getResult();
    }
}
