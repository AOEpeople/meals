<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * the Dish Repository
 * Class DishRepository
 * @package Mealz\MealBundle\Entity
 */
class DishRepository extends LocalizedRepository
{

    protected $defaultOptions = array(
        'load_category' => true,
        'orderBy_category' => true,
        'load_disabled' => false,
        'load_disabled_variations' => false,
    );

    /**
     * Return a querybuilder that fetches all dish that have NO variations
     * and all variations without their dishes
     *
     * @return QueryBuilder
     */
    public function getDishesAndVariations()
    {
        $qb = $this->createQueryBuilder('d');
        $qb2 = $this->createQueryBuilder('s');

        $qb2->select('IDENTITY(s.parent)');
        $qb2->where('IDENTITY(s.parent) is not null');
        $qb2->distinct(true);

        $qb->select('d');
        $qb->where(
            $qb->expr()->notIn('d.id', $qb2->getDQL())
        );

        return $qb;
    }

    /**
     * get a query builder for sorted list of dishes
     *
     * @param array $options
     * @return QueryBuilder
     */
    public function getSortedDishesQueryBuilder($options = array())
    {
        $currentLocale = $this->localizationListener->getLocale();

        $options = array_merge($this->defaultOptions, $options);

        $qb = $this->createQueryBuilder('d');

        // SELECT
        $select = 'd';
        if ($options['load_category']) {
            $select .= ',c';
        }

        // JOIN
        if ($options['load_category']) {
            $qb->leftJoin('d.category', 'c');
        }

        // WHERE
        if (!$options['load_disabled']) {
            $qb->where('d.enabled = 1');
        }

        // ORDER BY
        if ($options['load_category'] && $options['orderBy_category']) {
            $qb->orderBy('c.title_'.$currentLocale);
            $qb->addOrderBy('d.title_'.$currentLocale);
        } else {
            $qb->orderBy('d.title_'.$currentLocale, 'DESC');
        }

        return $qb;
    }

    /**
     * @param Dish $dish
     * @return integer
     */
    public function hasDishAssociatedMeals(Dish $dish)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(m.dish)');
        $qb->from('Mealz\MealBundle\Entity\Meal', 'm');
        $qb->where('m.dish = :dish');
        $qb->setParameter('dish', $dish);

        return $qb->getQuery()->getSingleScalarResult();
    }
}