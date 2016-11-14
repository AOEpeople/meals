<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class MealRepository extends EntityRepository
{
    /**
     * @param string $date "YYYY-MM-DD"
     * @param string $dish slug of the dish
     * @param array $userSelections already selected meals for that day
     * @return mixed|null
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function findOneByDateAndDish($date, $dish, $userSelections = array())
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/ims', $date)) {
            throw new \InvalidArgumentException('$date has to be a string of the format "YYYY-MM-DD".');
        }
        if ($date instanceof \DateTime) {
            $date = $date->format('Y-m-d');
        }

        $qb = $this->createQueryBuilder('m');

        // SELECT
        $qb->addSelect('d');

        // JOIN
        $qb->leftJoin('m.dish', 'd');

        // WHERE
        $qb->andWhere('m.dateTime >= :min_date');
        $qb->andWhere('m.dateTime <= :max_date');
        $qb->setParameter('min_date', $date.' 00:00:00');
        $qb->setParameter('max_date', $date.' 23:59:29');

        $qb->andWhere('d.slug = :dish');
        $qb->setParameter('dish', $dish);

        $result = $qb->getQuery()->execute();

        if (count($result) > 1) {
            $results = $result;
            // this is actually a logical error, when there are 2 identical Dish for same day, but we try to handle it
            // @TODO: should be disabled in BE to select 1 Dish twice on same day
            foreach ($results as $key => $meal) {
                if (in_array($meal->getId(), $userSelections)) {
                    unset($result[$key]);
                }
            }
//            throw new \LogicException('Found more then 2 meals matching the given requirements.');
        }

        return $result ? current($result) : null;
    }
}