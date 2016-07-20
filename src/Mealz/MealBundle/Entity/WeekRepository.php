<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;

class WeekRepository extends EntityRepository
{
    protected $defaultOptions = array(
        'load_participants' => true,
        'only_enabled_days' => false
    );

    public function getCurrentWeek($options = array())
    {
        $now = new \DateTime();
        return $this->findWeekByDate($now, $options);
    }

    public function getNextWeek($options = array())
    {
        $nextWeek = new \DateTime('next week');
        return $this->findWeekByDate($nextWeek);
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
        if (TRUE === $options['only_enabled_days']) {
            $qb->andWhere('da.enabled = 1');
        }

        $qb->setParameter('year', $date->format('Y'));
        $qb->setParameter('calendarWeek', $date->format('W'));

        return $qb->getQuery()->getOneOrNullResult();
    }
}