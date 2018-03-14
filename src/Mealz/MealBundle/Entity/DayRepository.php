<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DayRepository extends EntityRepository
{

    public function getCurrentDay()
    {
        $qb = $this->createQueryBuilder('d');
        $qb->where('d.dateTime LIKE :today');
        $qb->setParameter(':today', date('Y-m-d%'));

        return $qb->getQuery()->getSingleResult();
    }

}