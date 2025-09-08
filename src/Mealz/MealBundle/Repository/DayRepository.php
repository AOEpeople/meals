<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Day;
use DateTime;
use Doctrine\ORM\Query\Expr\Join;
use Override;

/**
 * @extends BaseRepository<int, Day>
 */
final class DayRepository extends BaseRepository implements DayRepositoryInterface
{
    #[Override]
    public function getCurrentDay(): ?Day
    {
        return $this->getDay(date('Y-m-d'));
    }

    #[Override]
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
        if (is_array($result) && count($result) >= 1) {
            return array_shift($result);
        }

        return null;
    }

    #[Override]
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
     * @psalm-return Day::class
     */
    #[Override]
    public function getClassName(): string
    {
        return Day::class;
    }
}
