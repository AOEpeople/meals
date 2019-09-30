<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DayRepository extends EntityRepository
{
    public function getCurrentDay()
    {
        $query = $this->createQueryBuilder('d');
        $query->where('d.dateTime LIKE :today');
        $query->setParameter(':today', date('Y-m-d%'));

        return $query->getQuery()->getSingleResult();
    }
}
