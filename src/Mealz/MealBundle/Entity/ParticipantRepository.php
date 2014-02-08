<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class ParticipantRepository extends EntityRepository {

	protected $defaultOptions = array(
		'load_meal' => FALSE,
		'load_profile' => TRUE,
	);

	/**
	 * @param $options
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function getQueryBuilderWithOptions($options)
	{
		$qb = $this->createQueryBuilder('p');

		// SELECT
		$select = 'p';
		if ($options['load_meal']) {
			$select .= ',m,d';
		}
		if($options['load_profile']) {
			$select .= ',u';
		}
		$qb->select($select);

		// JOIN
		if ($options['load_meal']) {
			$qb->leftJoin('p.meal', 'm');
			$qb->leftJoin('m.dish', 'd');
		}
		if ($options['load_profile']) {
			$qb->leftJoin('p.profile', 'u');
		}
		return $qb;
	}

	public function getParticipantsOnDay(\DateTime $date, $options = array()) {
		$options = array_merge($options, array(
			'load_meal' => TRUE,
			'load_profile' => TRUE,
		));
		$qb = $this->getQueryBuilderWithOptions($options);

		$minDate = clone $date;
		$minDate->setTime(0,0,0);
		$maxDate = clone $minDate;
		$maxDate->modify('+1 day -1 second');

		$qb->andWhere('m.dateTime >= :minDate');
		$qb->andWhere('m.dateTime <= :maxDate');
		$qb->setParameter('minDate', $minDate);
		$qb->setParameter('maxDate', $maxDate);

		$qb->orderBy('u.name', 'ASC');

		$participants = $qb->getQuery()->execute();

		return $this->sortParticipantsByName($participants);
	}

	/**
	 * helper function to sort participants by their name or guest name
	 */
	public function sortParticipantsByName($participants) {
		usort($participants, array($this, 'compareNameOfParticipants'));
		return $participants;
	}

	protected function compareNameOfParticipants(Participant $participant1, Participant $participant2) {
		$name1 = $participant1->isGuest() ? $participant1->getGuestName() : $participant1->getProfile()->getName();
		$name2 = $participant2->isGuest() ? $participant2->getGuestName() : $participant2->getProfile()->getName();
		return strcasecmp($name1, $name2);
	}

}