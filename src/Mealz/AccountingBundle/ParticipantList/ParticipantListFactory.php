<?php
namespace App\Mealz\AccountingBundle\ParticipantList;

use DateTime;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\UserBundle\Entity\Profile;

class ParticipantListFactory
{

    /**
     * @var ParticipantRepository
     */
    protected $participantRepository;

    /**
     * @param ParticipantRepository $participantRepository
     */
    public function __construct(ParticipantRepository $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param DateTime $minDate
     * @param DateTime $maxDate
     * @param Profile $profile
     * @return ParticipantList
     */
    public function getList(DateTime $minDate, DateTime $maxDate, Profile $profile = null)
    {
        $participants = $this->participantRepository->getParticipantsOnDays($minDate, $maxDate, $profile);

        return new ParticipantList($participants);
    }
}
