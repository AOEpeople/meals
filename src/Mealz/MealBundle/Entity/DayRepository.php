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

    public function getTodayAndTomorrow() {
        $qb = $this->createQueryBuilder('d');
        $qb->where('d.dateTime LIKE :today OR d.dateTime LIKE :tomorrow');
        $qb->setParameter(':today', date('Y-m-d%'));
        $qb->setParameter(':tomorrow', date('Y-m-d%', strtotime(' +1 day', time())));

        return $qb->getQuery()->execute();
    }


}