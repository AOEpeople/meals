<?php

namespace App\Mealz\AccountingBundle\ParticipantList;

use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\UserBundle\Entity\Profile;

class ParticipantList
{
    /**
     * @var Participant[]
     */
    protected $participations;

    /**
     * @var Profile[]
     */
    protected $profiles = null;

    /**
     * @param \App\Mealz\MealBundle\Entity\Participant[] $participations
     */
    public function __construct(array $participations)
    {
        $this->participations = $participations;
    }

    /**
     * @return Profile[]
     */
    public function getProfiles()
    {
        if (null === $this->profiles) {
            $this->profiles = array_unique(array_map(function (Participant $participant) {
                return $participant->getProfile();
            }, $this->participations));
        }

        return $this->profiles;
    }

    /**
     * @return Participant[]
     */
    public function getParticipations(Profile $profile)
    {
        $participations = array_filter($this->participations, function (Participant $participant) use ($profile) {
            return $participant->getProfile() === $profile;
        });

        sort($participations);

        return $participations;
    }

    /**
     * @return int
     */
    public function countParticipations(Profile $profile)
    {
        return count($this->getParticipations($profile));
    }

    /**
     * @return Participant[]
     */
    public function getAccountableParticipations(Profile $profile)
    {
        $participations = array_filter($this->participations, function (Participant $participant) use ($profile) {
            return $participant->getProfile() === $profile && $participant->isAccountable();
        });

        sort($participations);

        return $participations;
    }

    /**
     * @return int
     */
    public function countAccountableParticipations(Profile $profile)
    {
        $price = 0;
        $participations = $this->getAccountableParticipations($profile);
        foreach ($participations as $participation) {
            $price += $participation->getMeal()->getPrice();
        }

        return $price;
    }
}
