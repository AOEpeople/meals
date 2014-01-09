<?php


namespace Mealz\MealBundle\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Mealz\UserBundle\Entity\Zombie;

class ParticipantRepository extends EntityRepository {

	/**
	 * add a participant while assuring consistency by using a transaction
	 *
	 * @param Participant $participant
	 * @throws \Exception
	 */
	public function addParticipant(Participant $participant) {
		$em = $this->getEntityManager();
		$em->getConnection()->beginTransaction();

		try {
			if($this->checkIfIsParticipant($participant->getUser(), $participant->getMeal())) {
				// if user is already registered
				throw new \InvalidArgumentException(sprintf(
					'User %s already joined %s.',
					$participant->getUser(),
					$participant->getMeal()
				));
			}

			$em->persist($participant);
			$em->flush();
			$em->getConnection()->commit();
		} catch (\Exception $e) {
			$em->getConnection()->rollback();
			$em->close();
			throw $e;
		}
	}

	/**
	 * remove a participant while assuring consistency by using a transaction
	 *
	 * @param Zombie $user
	 * @param Meal $meal
	 * @throws \Exception
	 * @return integer number of deleted records
	 */
	public function removeParticipantByUserAndMeal(Zombie $user, Meal $meal) {
		$em = $this->getEntityManager();
		$em->getConnection()->beginTransaction();

		try {
			$query = $this->getEntityManager()->createQuery('
				SELECT p.id
				FROM MealzMealBundle:Participant p
				JOIN p.meal m
				JOIN p.user u
				WHERE m = :meal AND u = :user
			');
			$query->setParameters(array(
				'meal' => $meal->getId(),
				'user' => $user->getUsername()
			));

			$participantIds = $query->execute(null,Query::HYDRATE_SCALAR);
			if(empty($participantIds)) {
				throw new \InvalidArgumentException(sprintf(
					'User %s did not join %s.',
					$user,
					$meal
				));
			}

			$count = $this->getEntityManager()->createQuery('
				DELETE FROM MealzMealBundle:Participant p
				WHERE p.id IN (:ids)
			')->execute(array('ids' => $participantIds));

			$em->getConnection()->commit();

			return $count;
		} catch (\Exception $e) {
			$em->getConnection()->rollback();
			$em->close();
			throw $e;
		}
	}

	public function isParticipant(Zombie $user, Meal $meal)  {
		/* @TODO: the idea is to have some kind of preloading here, so just one query is needed to fetch the
		/* info which meals in a list the currently logged in user has booked.
		 */

		// @TODO: some kind of cache

		return $this->checkIfIsParticipant($user, $meal);
	}

	/**
	 * @param Zombie $user
	 * @param Meal $meal
	 * @return bool
	 */
	protected function checkIfIsParticipant(Zombie $user, Meal $meal) {
		/** @var Query $query */
		$query = $this->getEntityManager()->createQuery('
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

}