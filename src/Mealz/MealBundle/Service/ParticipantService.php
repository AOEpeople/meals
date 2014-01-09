<?php


namespace Mealz\MealBundle\Service;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Meal;
use Mealz\UserBundle\Entity\Zombie;

class ParticipantService extends \Twig_Extension {

	/**
	 * @var EntityManager
	 */
	protected $entityManager;

	public function __construct(EntityManager $entityManager) {
		$this->entityManager = $entityManager;
	}

	public function getFilters() {
		return array(
			'is_participant' => new \Twig_Filter_Method($this, 'isParticipant'),
		);
	}

	/**
	 * return TRUE if the given user has booked the meal
	 *
	 * @param Zombie $user
	 * @param Meal $meal
	 * @return bool
	 */
	public function isParticipant(Zombie $user, Meal $meal)  {
		// @TODO: the idea is to have some kind of preloading here, so just one query is needed to fetch the
		// info which meals in a list the currently logged in user has booked.
		/** @var Query $query */
		$query = $this->entityManager->createQuery('
			SELECT COUNT(p.id)
			FROM MealzMealBundle:Participant p
			JOIN p.meal m
			JOIN p.user u
			WHERE m = :meal AND u = :user
		');
		$query->setParameters(array(
			'meal' => $meal->getId(),
			'user' => $user->getUsername()
		));

		return $query->execute(null, Query::HYDRATE_SINGLE_SCALAR) > 0;

	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName() {
		return 'participant';
	}
}