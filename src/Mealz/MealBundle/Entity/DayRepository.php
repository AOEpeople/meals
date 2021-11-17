<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

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

    /**
     * Get all active meal days between $startDate and $endDate.
     *
     * @return Day[]
     */
    public function findAllActive(DateTime $startDate, DateTime $endDate): array
    {
        $queryBuilder = $this->createQueryBuilder('d');
        $queryBuilder
            ->join('d.week', 'w', Join::WITH, 'w.enabled = 1')
            ->where('d.dateTime > :startDate AND d.dateTime <= :endDate AND d.enabled = 1')
            ->setParameters([
                'startDate' => (clone $startDate)->setTime(0, 0),
                'endDate' => (clone $endDate)->setTime(12, 0, 0)
            ]);

        return $queryBuilder->getQuery()->getResult();
    }
}
