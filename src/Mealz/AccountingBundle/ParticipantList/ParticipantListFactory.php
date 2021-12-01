<?php

namespace App\Mealz\AccountingBundle\ParticipantList;

use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;

class ParticipantListFactory
{
    /**
     * @var ParticipantRepository
     */
    protected $participantRepository;

    public function __construct(ParticipantRepository $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param Profile $profile
     *
     * @return ParticipantList
     */
    public function getList(DateTime $minDate, DateTime $maxDate, Profile $profile = null)
    {
        $participants = $this->participantRepository->getParticipantsOnDays($minDate, $maxDate, $profile);

        return new ParticipantList($participants);
    }
}
