<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Mealz\UserBundle\Entity\Profile;

class ParticipantRepository extends EntityRepository
{

	const COLUM_PRICE = 'price';

	protected $defaultOptions = array(
		'load_meal' => false,
		'load_profile' => true,
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
		if ($options['load_profile']) {
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

	public function getParticipantsOnDays(
		\DateTime $minDate,
		\DateTime $maxDate,
		Profile $profile = null,
		$options = array()
	) {
		$options = array_merge($options, array(
			'load_meal' => true,
			'load_profile' => true,
		));
		if ($profile) {
			$options['load_profile'] = true;
		}
		$qb = $this->getQueryBuilderWithOptions($options);

		$minDate = clone $minDate;
		$minDate->setTime(0, 0, 0);
		$maxDate = clone $maxDate;
		$maxDate->setTime(23, 59, 59);

		$qb->andWhere('m.dateTime >= :minDate');
		$qb->andWhere('m.dateTime <= :maxDate');
		$qb->setParameter('minDate', $minDate);
		$qb->setParameter('maxDate', $maxDate);

		if ($profile) {
			$qb->andWhere('u.username = :username');
			$qb->setParameter('username', $profile->getUsername());
		}

		$qb->orderBy('u.name', 'ASC');

		$participants = $qb->getQuery()->execute();

		return $this->sortParticipantsByName($participants);
	}

	/**
	 * helper function to sort participants by their name or guest name
	 */
	public function sortParticipantsByName($participants)
	{
		usort($participants, array($this, 'compareNameOfParticipants'));
		return $participants;
	}

	protected function compareNameOfParticipants(Participant $participant1, Participant $participant2)
	{
		$name1 = $participant1->isGuest() ? $participant1->getGuestName() : $participant1->getProfile()->getName();
		$name2 = $participant2->isGuest() ? $participant2->getGuestName() : $participant2->getProfile()->getName();
		$result = strcasecmp($name1, $name2);

		if ($result !== 0) {
			return $result;
		} elseif ($participant1->getMeal()->getDateTime() < $participant2->getMeal()->getDateTime()) {
			return 1;
		} elseif ($participant1->getMeal()->getDateTime() > $participant2->getMeal()->getDateTime()) {
			return -1;
		}

		return 0;
	}

	/**
	 * Get total costs of participations. Prevent unnecessary ORM mapping.
	 *
	 * @param Profile $profile
	 * @return float
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function getTotalCost(Profile $profile)
	{
		$qb = $this->getQueryBuilderWithOptions([
			'load_meal' => true,
			'load_profile' => false,
		]);

		$qb->select('SUM(m.price) as blubber');
		$qb->andWhere('p.profile = :user');
		$qb->setParameter('user', $profile);
		$qb->andWhere('p.costAbsorbed = :costAbsorbed');
		$qb->setParameter('costAbsorbed', false);
		$qb->andWhere('m.dateTime <= :now');
		$qb->setParameter('now', new \DateTime());

		return floatval($qb->getQuery()->getSingleScalarResult());
	}

	/**
	 * @param Profile $profile
	 * @param int $limit
	 * @return Participant[]
	 */
	public function getLastAccountableParticipations(Profile $profile, $limit = null)
	{
		$qb = $this->getQueryBuilderWithOptions([
			'load_meal' => true,
			'load_profile' => false,
		]);

		$qb->andWhere('p.profile = :user');
		$qb->setParameter('user', $profile);
		$qb->andWhere('p.costAbsorbed = :costAbsorbed');
		$qb->setParameter('costAbsorbed', false);
		$qb->andWhere('m.dateTime <= :now');
		$qb->setParameter('now', new \DateTime());

		$qb->orderBy('m.dateTime', 'desc');
		if ($limit) {
			$qb->setMaxResults($limit);
		}

		return $qb->getQuery()->execute();
	}
}