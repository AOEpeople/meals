<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\ParticipantList;

use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;

class ParticipantListFactory
{
    private ParticipantRepositoryInterface $participantRepository;

    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    public function getList(DateTime $minDate, DateTime $maxDate, ?Profile $profile = null): ParticipantList
    {
        $participants = $this->participantRepository->getParticipantsOnDays($minDate, $maxDate, $profile);

        return new ParticipantList($participants);
    }
}
