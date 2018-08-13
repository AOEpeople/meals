<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class WeekRepository
 * @package Mealz\MealBundle\Entity
 */
class WeekRepository extends EntityRepository
{
    protected $defaultOptions = array(
        'load_participants' => true,
        'only_enabled_days' => false
    );

    /**
     * @param  array $options
     * @return Week|NULL
     */
    public function getCurrentWeek($options = array())
    {
        return $this->findWeekByDate(new \DateTime(), $options);
    }

    /**
     * @param  \DateTime|NULL $date
     * @param  array $options
     * @return Week|NULL
     */
    public function getNextWeek(\DateTime $date = null, $options = array())
    {
        $date = (($date instanceof \DateTime) === false) ? new \DateTime() : $date;
        $nextWeek = $date->modify('next monday');

        return $this->findWeekByDate($nextWeek, $options);
    }

    /**
     * @param Week $week
     * @return mixed
     */
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
     * @param  array $options
     * @return null|Week
     */
    public function findWeekByDate(\DateTime $date, $options = array())
    {
        $options = array_merge($this->defaultOptions, $options);

        $qb = $this->createQueryBuilder('w');

        $select = 'w,da,m,d';
        if ($options['load_participants']) {
            $select .= ',p,u';
        }
        $qb->select($select);

        $qb->leftJoin('w.days', 'da');
        $qb->leftJoin('da.meals', 'm');
        $qb->leftJoin('m.dish', 'd');

        if ($options['load_participants']) {
            $qb->leftJoin('m.participants', 'p');
            $qb->leftJoin('p.profile', 'u');
        }

        $qb->andWhere('w.year = :year');
        $qb->andWhere('w.calendarWeek = :calendarWeek');
        if (true === $options['only_enabled_days']) {
            $qb->andWhere('da.enabled = 1');
        }

        $qb->setParameter('year', $date->format('Y'));
        $qb->setParameter('calendarWeek', $date->format('W'));

        return $qb->getQuery()->getOneOrNullResult();
    }
}
