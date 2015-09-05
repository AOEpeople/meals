<?php
namespace Mealz\AccountingBundle\ParticipantList;

use Mealz\MealBundle\Entity\ParticipantRepository;
use Mealz\UserBundle\Entity\Profile;

class ParticipantListFactory {

	/**
	 * @var ParticipantRepository
	 */
	protected $participantRepository;

	/**
	 * @param ParticipantRepository $participantRepository
	 */
	public function __construct(ParticipantRepository $participantRepository) {
		$this->participantRepository = $participantRepository;
	}

	/**
	 * @param \DateTime $minDate
	 * @param \DateTime $maxDate
	 * @param Profile $profile
	 * @return ParticipantList
	 */
	public function getList(\DateTime $minDate, \DateTime $maxDate, Profile $profile = NULL) {
		$participants = $this->participantRepository->getParticipantsOnDays($minDate, $maxDate, $profile);

		return new ParticipantList($participants);
	}


}
