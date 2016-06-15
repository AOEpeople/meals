<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class DishRepository extends EntityRepository {

	protected $defaultOptions = array(
		'load_category' => FALSE,
	);

	protected $currentLocale = 'en';

	/**
	 * @param string $currentLocale
	 */
	public function setCurrentLocale($currentLocale) {
		$this->currentLocale = $currentLocale;
	}

	/**
	 * get a sorted list of dishes
	 *
	 * @param array $options
	 * @return array
	 */
	public function getSortedDishes($options = array()) {
		$options = array_merge($this->defaultOptions, $options);

		$qb = $this->createQueryBuilder('d');

		// SELECT
		$select = 'd';
		if($options['load_category']) {
			$select .= ',c';
		}
		$qb->select($select);

		// JOIN
		if($options['load_category']) {
			$qb->leftJoin('d.category', 'c');
		}

		// WHERE
		$qb->where('d.enabled = 1');

		// ORDER BY
		$qb->orderBy('d.title_' . $this->currentLocale, 'DESC');

		return $qb->getQuery()->execute();
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