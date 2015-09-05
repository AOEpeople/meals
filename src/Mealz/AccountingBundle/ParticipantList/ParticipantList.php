<?php
namespace Mealz\AccountingBundle\ParticipantList;

use Mealz\MealBundle\Entity\Participant;
use Mealz\UserBundle\Entity\Profile;

class ParticipantList {

	/**
	 * @var Participant[]
	 */
	protected $participations;

	/**
	 * @var Profile[]
	 */
	protected $profiles = NULL;

	/**
	 * @param \Mealz\MealBundle\Entity\Participant[] $participations
	 */
	public function __construct(array $participations) {
		$this->participations = $participations;
	}

	/**
	 * @return Profile[]
	 */
	public function getProfiles() {
		if ($this->profiles === NULL) {
			$this->profiles = array_unique(array_map(function(Participant $participant) {
				return $participant->getProfile();
			}, $this->participations));
		}

		return $this->profiles;
	}

	/**
	 * @param Profile $profile
	 * @return Participant[]
	 */
	public function getParticipations(Profile $profile) {
		$participations = array_filter($this->participations, function(Participant $participant) use($profile) {
			return $participant->getProfile() === $profile;
		});

		sort($participations);

		return $participations;
	}

	/**
	 * @param Profile $profile
	 * @return int
	 */
	public function countParticipations(Profile $profile) {
		return count($this->getParticipations($profile));
	}

	/**
	 * @param Profile $profile
	 * @return Participant[]
	 */
	public function getAccountableParticipations(Profile $profile) {
		$participations = array_filter($this->participations, function (Participant $participant) use ($profile) {
			return $participant->getProfile() === $profile && $participant->isAccountable();
		});

		sort($participations);

		return $participations;
	}

	/**
	 * @param Profile $profile
	 * @return int
	 */
	public function countAccountableParticipations(Profile $profile) {
		return count($this->getAccountableParticipations($profile));
	}
}
