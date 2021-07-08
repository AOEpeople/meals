<?php
namespace Mealz\AccountingBundle\ParticipantList;

use DateTime;
use Mealz\MealBundle\Entity\ParticipantRepository;
use Mealz\UserBundle\Entity\Profile;

class ParticipantListFactory
{

    /**
     * @var ParticipantRepository
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected $participantRepository;

    /**
     * @param ParticipantRepository $participantRepository
     *
     * @SuppressWarnings(PHPMD.LongVariable)
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
