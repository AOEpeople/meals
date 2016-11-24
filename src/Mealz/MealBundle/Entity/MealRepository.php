<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * the Meal Repository
 * Class MealRepository
 * @package Mealz\MealBundle\Entity
 */
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
            foreach ($results as $key => $meal) {
                if (in_array($meal->getId(), $userSelections)) {
                    unset($result[$key]);
                }
            }
        }

        return $result ? current($result) : null;
    }


    /**
     * Created for Test with Dish variations
     *
     * @return mixed
     */
    public function getMealsOnADayWithVariationOptions()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT m.id
                FROM MealzMealBundle:Meal m
                WHERE m.day IN
                  (SELECT IDENTITY (ml.day) FROM MealzMealBundle:Meal ml GROUP BY ml.day HAVING COUNT(ml.day)>2)'
        );

        return $query->getResult();
    }
}