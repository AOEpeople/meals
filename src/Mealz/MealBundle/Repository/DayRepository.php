<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Day;
use DateTime;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @extends BaseRepository<Day>
 */
class DayRepository extends BaseRepository implements DayRepositoryInterface
{
    public function getCurrentDay(): ?Day
    {
        return $this->getDay(date('Y-m-d'));
    }

    public function getDayByDate(DateTime $dateTime): ?Day
    {
        return $this->getDay($dateTime->format('Y-m-d'));
    }

    private function getDay(string $date): ?Day
    {
        $queryBuilder = $this->createQueryBuilder('d');
        $queryBuilder->where('d.dateTime LIKE :date');
        $queryBuilder->setParameter(':date', $date . '%');

        $result = $queryBuilder->getQuery()->getResult();
        if ($result && is_array($result) && count($result) >= 1) {
            return array_shift($result);
        }

        return null;
    }

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

    /**
     * @return string
     *
     * @psalm-return Day::class
     */
    public function getClassName(): string
    {
        return Day::class;
    }
}
