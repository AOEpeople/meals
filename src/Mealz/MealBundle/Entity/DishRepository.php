<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class DishRepository extends EntityRepository {

	protected $defaultOptions = array(
		'load_meals' => FALSE,
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
		if($options['load_meals']) {
			$select .= ',m';
		}
		$qb->select($select);

		// JOIN
		if($options['load_meals']) {
			$qb->leftJoin('d.meals', 'm');
		}

		// ORDER BY
		$qb->orderBy('d.title_' . $this->currentLocale, 'ASC');

		return $qb->getQuery()->execute();

	}
}