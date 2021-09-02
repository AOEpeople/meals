<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<Day>
 */
class DayRepository extends EntityRepository
{
    public function getCurrentDay(): ?Day
    {
        $queryBuilder = $this->createQueryBuilder('d');
        $queryBuilder->where('d.dateTime LIKE :today');
        $queryBuilder->setParameter(':today', date('Y-m-d%'));

        $result = $queryBuilder->getQuery()->getResult();
        if ($result && is_array($result) && count($result) >= 1) {
            return array_shift($result);
        }

        return null;
    }
}
