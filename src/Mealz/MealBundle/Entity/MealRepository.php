<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;

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

        $queryBuilder = $this->createQueryBuilder('m');

        // SELECT
        $queryBuilder->addSelect('d');

        // JOIN
        $queryBuilder->leftJoin('m.dish', 'd');

        // WHERE
        $queryBuilder->andWhere('m.dateTime >= :min_date');
        $queryBuilder->andWhere('m.dateTime <= :max_date');
        $queryBuilder->setParameter('min_date', $date.' 00:00:00');
        $queryBuilder->setParameter('max_date', $date.' 23:59:29');

        if (is_numeric($dish)) {
            $queryBuilder->andWhere('d.id = :dish');
        } else {
            $queryBuilder->andWhere('d.slug = :dish');
        }
        $queryBuilder->setParameter('dish', $dish);

        $result = $queryBuilder->getQuery()->execute();

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
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT m.id
                FROM MealzMealBundle:Meal m
                WHERE m.day IN
                  (SELECT IDENTITY (ml.day) FROM MealzMealBundle:Meal ml GROUP BY ml.day HAVING COUNT(ml.day)>2)'
        );

        return $query->getResult();
    }

    /**
     * Returns all meals that are going to take place in the future.
     * @return array
     */
    public function getFutureMeals()
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->where('m.dateTime >= :now');
        $queryBuilder->setParameter(':now', new \DateTime('now'));
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Returns all meals that are going to took place in the past.
     * @return array
     */
    public function getOutdatedMeals()
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->where('m.dateTime <= :now');
        $queryBuilder->setParameter(':now', new \DateTime('now'));
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Returns all meals that are going to take place in the future but aren't available to join/leave anymore.
     * @return array
     */
    public function getLockedMeals()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('m')
            ->from('MealzMealBundle:Meal', 'm')
            ->innerJoin('m.day', 'd')
            ->where('d.lockParticipationDateTime < :now')
            ->andWhere('m.dateTime > :now')
            ->orderBy('m.dateTime', 'DESC');

        $queryBuilder->setParameter(':now', new \DateTime('now'));
        return $queryBuilder->getQuery()->getResult();
    }
}
