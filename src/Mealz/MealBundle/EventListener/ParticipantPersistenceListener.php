<?php


namespace Mealz\MealBundle\EventListener;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Participant;

/**
 * listener that ensures that there won't be duplicate entries for the same participant in the database
 */
class ParticipantPersistenceListener {

	public function prePersist(LifecycleEventArgs $args) {
		$entity = $args->getEntity();
		$entityManager = $args->getEntityManager();

		if($entity instanceof Participant) {
			$this->checkUniqueParticipant($entity, $entityManager);
		}
	}

	public function preUpdate(LifecycleEventArgs $args) {
		$entity = $args->getEntity();
		$entityManager = $args->getEntityManager();

		if($entity instanceof Participant) {
			$this->checkUniqueParticipant($entity, $entityManager);
		}
	}

	protected function checkUniqueParticipant(Participant $participant, EntityManager $em) {
		if($em->getConnection()->getTransactionNestingLevel() < 1) {
			throw new \RuntimeException(sprintf(
				'Participants can only be updated inside a transaction to ensure consistency. See http://docs.doctrine-project.org/en/latest/reference/transactions-and-concurrency.html#approach-2-explicitly'
			));
		}
		if($this->participantExists($participant, $em)) {
			throw new ParticipantNotUniqueException(
				'This participant has alredy joined: '. $participant
			);
		}
	}

	protected function participantExists(Participant $participant, EntityManager $entityManager) {
		$qb = $entityManager->createQueryBuilder();

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
			$qb->andWhere('p.guestName IS NULL');
		}
		if($participant->getId()) {
			$qb->andWhere('p.id != :id');
			$qb->setParameter('id', $participant->getId());
		}
		/** @var Query $query */
		$query = $qb->getQuery();
		$query->setParameter('meal', $participant->getMeal()->getId());
		$query->setParameter('user', $participant->getUser()->getUsername());
		$query->useResultCache(false);
		return $query->execute(null, Query::HYDRATE_SINGLE_SCALAR) > 0;
	}

}