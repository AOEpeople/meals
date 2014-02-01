<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class MealRepository extends EntityRepository {

	protected $defaultOptions = array(
		'load_dish' => FALSE,
		'load_participants' => FALSE,
	);

	/**
	 * get a sorted list of meals
	 *
	 * @param null|\DateTime $minDate
	 * @param null|\DateTime $maxDate
	 * @param null|integer $limit
	 * @param array $options
	 * @throws \BadMethodCallException
	 * @return mixed
	 */
	public function getSortedMeals($minDate = NULL, $maxDate = NULL, $limit = NULL, $options = array()) {
		$options = array_merge($this->defaultOptions, $options);

		if($options['load_participants'] && $limit) {
			/* if you need to fix this you have to do two queries: One to get the uids of all meals that you
			 * need to display (set the limit there) and then do a second query to actually map the
			 * objects and the relations (without the limit)
			 */
			throw new \BadMethodCallException(
				'Setting "load_participants" together with a "limit" will probably not do what you expect. ' .
				'It will not only limit the number of meals, but technically the number of participants, ' .
				'because this data is joined. See the code comment for how to fix this.'
			);

		}

		$qb = $this->createQueryBuilder('m');

		// SELECT
		$select = 'm';
		if($options['load_dish']) {
			$select .= ',d';
		}
		if($options['load_participants']) {
			$select .= ',p,u';
		}
		$qb->select($select);

		// JOIN
		if($options['load_dish']) {
			$qb->leftJoin('m.dish', 'd');
		}
		if($options['load_participants']) {
			$qb->leftJoin('m.participants', 'p');
			$qb->leftJoin('p.profile', 'u');
		}

		// WHERE
		if($minDate) {
			$qb->andWhere('m.dateTime >= :min_date');
			$qb->setParameter('min_date', $minDate);
		}
		if($maxDate) {
			$qb->andWhere('m.dateTime <= :max_date');
			$qb->setParameter('max_date', $maxDate);
		}

		// ORDER BY
		$qb->orderBy('m.dateTime', 'ASC');

		if($limit) {
			$qb->setMaxResults($limit);
		}

		return $qb->getQuery()->execute();

	}

	/**
	 * @param $id
	 * @param array $options
	 * @return Meal|null
	 */
	public function findOneById($id, $options = array()) {
		$options = array_merge($this->defaultOptions, $options);

		$qb = $this->createQueryBuilder('m');

		// SELECT
		$select = 'm';
		if($options['load_dish']) {
			$select .= ',d';
		}
		if($options['load_participants']) {
			$select .= ',p,u';
		}
		$qb->select($select);

		// JOIN
		if($options['load_dish']) {
			$qb->leftJoin('m.dish', 'd');
		}
		if($options['load_participants']) {
			$qb->leftJoin('m.participants', 'p');
			$qb->leftJoin('p.profile', 'u');
		}

		// WHERE
		$qb->andWhere('m.id = :meal_id');
		$qb->setParameter('meal_id', $id);

		$result = $qb->getQuery()->execute();
		return $result ? current($result) : NULL;
	}

}