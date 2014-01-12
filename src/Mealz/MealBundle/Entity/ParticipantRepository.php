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
			if($this->participantExists($participant->getUser(), $participant->getMeal(), $participant->getGuestName())) {
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
	 * @param Zombie $user
	 * @param Meal $meal
	 * @param $guestName
	 * @return bool
	 */
	protected function participantExists(Zombie $user, Meal $meal, $guestName) {
		$qb = $this->getEntityManager()->createQueryBuilder();

		$qb
			->select('COUNT(p.id)')
			->from('MealzMealBundle:Participant', 'p')
			->join('p.meal', 'm')
			->join('p.user','u')
			->where('m = :meal AND u = :user')
		;
		if($guestName) {
			$qb->andWhere('p.guestName = :guestName');
			$qb->setParameter('guestName', $guestName);
		} else {
			$qb->andWHere('p.guestName IS NULL');
		}
		/** @var Query $query */
		$query = $qb->getQuery();
		$query->setParameter('meal', $meal->getId());
		$query->setParameter('user', $user->getUsername());

		return $query->execute(null, Query::HYDRATE_SINGLE_SCALAR) > 0;
	}

}