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
     * An active meal day is the day that is not disabled, and has an open meal, i.e. meal that is open for participation.
     *
     * @return Day[]
     */
    public function findAllActive(DateTime $startDate, DateTime $endDate): array
    {
        $queryBuilder = $this->createQueryBuilder('d');
        $queryBuilder
            ->join('d.week', 'w', Join::WITH, 'w.enabled = 1')
            ->where('d.enabled = 1 AND d.dateTime > :now AND d.dateTime >= :startDate AND d.dateTime <= :endDate')
            ->setParameters([
                'now' => new DateTime(),
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);

        return $queryBuilder->getQuery()->getResult();
    }
}
