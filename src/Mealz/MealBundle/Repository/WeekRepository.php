<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Week;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * Class WeekRepository.
 */
class WeekRepository extends BaseRepository implements WeekRepositoryInterface
{
    protected array $defaultOptions = [
        'load_participants' => true,
        'only_enabled_days' => false,
    ];

    public function getCurrentWeek(array $options = []): ?Week
    {
        return $this->findWeekByDate(new DateTime(), $options);
    }

    public function getNextWeek(?DateTime $date = null, array $options = []): ?Week
    {
        $date = (($date instanceof DateTime) === false) ? new DateTime() : $date;
        $nextWeek = $date->modify('next monday');

        return $this->findWeekByDate($nextWeek, $options);
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getWeeksMealCount(Week $week): int
    {
        $query = $this->createQueryBuilder('w');
        $query->leftJoin('w.days', 'days');
        $query->leftJoin('days.meals', 'meals');
        $query->select($query->expr()->count('meals'));
        $query->where('w.id = ?1')
            ->setParameter(1, $week->getId());

        return $query->getQuery()->getSingleScalarResult();
    }

    public function findWeekByDate(DateTime $date, array $options = []): ?Week
    {
        $options = array_merge($this->defaultOptions, $options);

        $query = $this->createQueryBuilder('w');

        $select = 'w,da,m,d';
        if ($options['load_participants']) {
            $select .= ',p,u';
        }
        $query->select($select);

        $query->leftJoin('w.days', 'da');
        $query->leftJoin('da.meals', 'm');
        $query->leftJoin('m.dish', 'd');

        if ($options['load_participants']) {
            $query->leftJoin('m.participants', 'p');
            $query->leftJoin('p.profile', 'u');
        }

        $query->andWhere('w.year = :year');
        $query->andWhere('w.calendarWeek = :calendarWeek');
        if (true === $options['only_enabled_days']) {
            $query->andWhere('da.enabled = 1');
        }

        $query->setParameter('year', $date->format('o'));
        $query->setParameter('calendarWeek', $date->format('W'));

        return $query->getQuery()->getOneOrNullResult();
    }
}
