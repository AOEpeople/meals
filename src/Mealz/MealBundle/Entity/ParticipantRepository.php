<?php


namespace Mealz\MealBundle\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Mealz\UserBundle\Entity\User;

class ParticipantRepository extends EntityRepository {

	/**
	 * add a participant while assuring consistency by using a transaction
	 *
	 * @param Participant $participant
	 * @throws \Exception
	 */
	public function persist(Participant $participant) {
		$em = $this->getEntityManager();
		$em->getConnection()->beginTransaction();

		try {
			if($this->participantExists($participant)) {
				// if user is already registered
				throw new \InvalidArgumentException(
					'This participant has alredy joined: '. $participant
				);
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
	 * @param Participant $participant
	 * @return bool
	 */
	protected function participantExists(Participant $participant) {
		$qb = $this->getEntityManager()->createQueryBuilder();

		$qb
			->select('COUNT(p.id)')
			->from('MealzMealBundle:Participant', 'p')
			->join('p.meal', 'm')
			->join('p.user','u')
			->where('m = :meal AND u = :user')
		;
		if($participant->isGuest()) {
			$qb->andWhere('p.guestName = :guestName');
			$qb->setParameter('guestName', $participant->getGuestName());
		} else {
			$qb->andWHere('p.guestName IS NULL');
		}
		if($participant->getId()) {
			$qb->andWhere('p.id != :id');
			$qb->setParameter('id', $participant->getId());
		}
		/** @var Query $query */
		$query = $qb->getQuery();
		$query->setParameter('meal', $participant->getMeal()->getId());
		$query->setParameter('user', $participant->getUser()->getUsername());

		return $query->execute(null, Query::HYDRATE_SINGLE_SCALAR) > 0;
	}

}