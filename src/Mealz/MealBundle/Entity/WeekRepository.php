<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;

class WeekRepository extends EntityRepository
{
    /**
     * @param  bool $onlyEnabledDays
     * @return Week|NULL
     */
    public function getCurrentWeek($onlyEnabledDays = FALSE)
    {
        return $this->findWeekByDate(new \DateTime(), $onlyEnabledDays);
    }

    /**
     * @param  \DateTime|NULL $date
     * @param  bool           $onlyEnabledDays
     * @return Week|NULL
     */
    public function getNextWeek(\DateTime $date = NULL, $onlyEnabledDays = FALSE)
    {
        $date = is_null($date) ? new \DateTime() : $date;
        $nextWeek = $date->modify('next monday');
        return $this->findWeekByDate($nextWeek, $onlyEnabledDays);
    }

    public function getWeeksMealCount(Week $week)
    {
        $qb = $this->createQueryBuilder('w');
        $qb->leftJoin('w.days', 'days');
        $qb->leftJoin('days.meals', 'meals');
        $qb->select($qb->expr()->count('meals'));
        $qb->where('w.id = ?1')
            ->setParameter(1, $week->getId());
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param \DateTime $date
     * @param  boolean  $onlyEnabledDays
     * @return null|Week
     */
    public function findWeekByDate(\DateTime $date, $onlyEnabledDays = FALSE)
    {
        $qb = $this->createQueryBuilder('w');
        $qb->select('w,da,m,d,p,u');

        $qb->leftJoin('w.days', 'da');
        $qb->leftJoin('da.meals', 'm');
        $qb->leftJoin('m.dish', 'd');
        $qb->leftJoin('m.participants', 'p');
        $qb->leftJoin('p.profile', 'u');

        $qb->andWhere('w.year = :year');
        $qb->andWhere('w.calendarWeek = :calendarWeek');
        if (TRUE === $onlyEnabledDays) {
            $qb->andWhere('da.enabled = 1');
        }

        $qb->setParameter('year', $date->format('Y'));
        $qb->setParameter('calendarWeek', $date->format('W'));

        return $qb->getQuery()->getOneOrNullResult();
    }
}
