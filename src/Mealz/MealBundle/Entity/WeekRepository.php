<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;

class WeekRepository extends EntityRepository
{
    public function getCurrentWeek()
    {
        $now = new \DateTime();
        return $this->findWeekByDate($now);
    }

    public function getNextWeek()
    {
        $nextWeek = new \DateTime('next week');
        return $this->findWeekByDate($nextWeek);
    }

    public function getWeeksMealCount(Week $week)
    {
        $qb = $this->createQueryBuilder('w');
        $qb->leftJoin('w.days', 'days');
        $qb->leftJoin('days.meals', 'meals');
        // $qb->expr()->count('meal')
        $qb->select($qb->expr()->count('meals'));
        $qb->where('w.id = ?1')
            ->setParameter(1, $week->getId());
        return $qb->getQuery()->getSingleScalarResult();
    }

    private function findWeekByDate(\DateTime $date)
    {
        return $this->findOneBy(array(
            'year' => $date->format('Y'),
            'calendarWeek' => $date->format('W')
        ));
    }
}