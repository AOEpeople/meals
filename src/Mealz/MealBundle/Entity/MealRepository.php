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

		$qb = $this->getQueryBuilderWithOptions($options);

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
	 * @param \DateTime $date
	 * @param null $limit
	 * @param array $options
	 * @return mixed
	 */
	public function getSortedMealsOnDay(\DateTime $date, $limit = NULL, $options = array()) {
		$minDate = clone $date;
		$minDate->setTime(0,0,0);
		$maxDate = clone $minDate;
		$maxDate->modify('+1 day -1 second');

		return $this->getSortedMeals($minDate, $maxDate, $limit, $options);
	}

	/**
	 * @param $id
	 * @param array $options
	 * @return Meal|null
	 */
	public function findOneById($id, $options = array()) {
		$options = array_merge($this->defaultOptions, $options);

		$qb = $this->getQueryBuilderWithOptions($options);

		// WHERE
		$qb->andWhere('m.id = :meal_id');
		$qb->setParameter('meal_id', $id);

		$result = $qb->getQuery()->execute();
		return $result ? current($result) : NULL;
	}

	/**
	 * @param string $date "YYYY-MM-DD"
	 * @param string $dish slug of the dish
	 * @param array $options
	 * @return mixed|null
	 * @throws \LogicException
	 * @throws \InvalidArgumentException
	 */
	public function findOneByDateAndDish($date, $dish, $options = array()) {
		$options = array_merge($this->defaultOptions, $options);
		$options['load_dish'] = TRUE;
		if(!preg_match('/^\d{4}-\d{2}-\d{2}$/ims', $date)) {
			throw new \InvalidArgumentException('$date has to be a string of the format "YYYY-MM-DD".');
		}
		if($date instanceof \DateTime) {
			$date = $date->format('Y-m-d');
		}

		$qb = $this->getQueryBuilderWithOptions($options);

		// WHERE
		$qb->andWhere('m.dateTime >= :min_date');
		$qb->andWhere('m.dateTime <= :max_date');
		$qb->setParameter('min_date', $date . ' 00:00:00');
		$qb->setParameter('max_date', $date . ' 23:59:29');

		$qb->andWhere('d.slug = :dish');
		$qb->setParameter('dish', $dish);

		$result = $qb->getQuery()->execute();

		if(count($result) > 1) {
			throw new \LogicException('Found more then one meal matching the given requirements.');
		}

		return $result ? current($result) : NULL;
	}

	/**
	 * @param $options
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function getQueryBuilderWithOptions($options)
	{
		$qb = $this->createQueryBuilder('m');

		// SELECT
		$select = 'm';
		if ($options['load_dish']) {
			$select .= ',d';
		}
		if ($options['load_participants']) {
			$select .= ',p,u';
		}
		$qb->select($select);

		// JOIN
		if ($options['load_dish']) {
			$qb->leftJoin('m.dish', 'd');
		}
		if ($options['load_participants']) {
			$qb->leftJoin('m.participants', 'p');
			$qb->leftJoin('p.profile', 'u');
			return $qb;
		}
		return $qb;
	}

	/**
	 * get the date of the last submitted meal
	 *
	 * @return \DateTime|null
	 */
	public function getLastMealDate() {
		$qb = $this->createQueryBuilder('m');

		$qb->select('m.dateTime');
		$qb->orderBy('m.dateTime', 'DESC');
		$qb->setMaxResults(1);

		$date = $qb->getQuery()->execute(array(), Query::HYDRATE_SINGLE_SCALAR);
		return $date ? new \DateTime($date) : NULL;
	}

	/**
	 * @param \DateTime $dateTime
	 * @return int
	 */
	public function countMealsAt(\DateTime $dateTime) {
		$qb = $this->createQueryBuilder('m');

		$qb->select('COUNT(m)');
		$qb->andWhere('m.dateTime = :dateTime');
		$qb->setParameter('dateTime', $dateTime);

		return intval($qb->getQuery()->execute(array(), Query::HYDRATE_SINGLE_SCALAR));
	}


}