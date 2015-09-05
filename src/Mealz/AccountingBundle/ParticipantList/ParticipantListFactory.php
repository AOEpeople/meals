<?php
namespace Mealz\AccountingBundle\ParticipantList;

use Mealz\MealBundle\Entity\ParticipantRepository;

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
	 * @return ParticipantList
	 */
	public function getList(\DateTime $minDate, \DateTime $maxDate) {
		$participants = $this->participantRepository->getParticipantsOnDays($minDate, $maxDate);

		return new ParticipantList($participants);
	}


}
