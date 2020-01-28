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
        $query = $this->createQueryBuilder('w');
        $query->leftJoin('w.days', 'days');
        $query->leftJoin('days.meals', 'meals');
        $query->select($query->expr()->count('meals'));
        $query->where('w.id = ?1')
            ->setParameter(1, $week->getId());

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param \DateTime $date
     * @param  array $options
     * @return null|Week
     */
    public function findWeekByDate(\DateTime $date, $options = array())
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
