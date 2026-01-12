<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\ParticipantList;

use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\UserBundle\Entity\Profile;

final class ParticipantList
{
    /**
     * @var Participant[]
     */
    protected $participations;

    /**
     * @var Profile[]
     */
    protected $profiles;

    /**
     * @param Participant[] $participations
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
     *
     * @psalm-return list<Participant>
     */
    public function getParticipations(Profile $profile): array
    {
        $participations = array_filter($this->participations, function (Participant $participant) use ($profile) {
            return $participant->getProfile() === $profile;
        });

        sort($participations);

        return $participations;
    }

    public function countParticipations(Profile $profile): int
    {
        return count($this->getParticipations($profile));
    }

    /**
     * @return Participant[]
     *
     * @psalm-return list<Participant>
     */
    public function getAccountableParticipations(Profile $profile): array
    {
        $participations = array_filter($this->participations, function (Participant $participant) use ($profile) {
            return $participant->getProfile() === $profile && $participant->isAccountable();
        });

        sort($participations);

        return $participations;
    }

    /**
     * @psalm-return 0|float
     */
    public function countAccountableParticipations(Profile $profile): int|float
    {
        $price = 0.0;
        $participations = $this->getAccountableParticipations($profile);
        foreach ($participations as $participation) {
            $mealPrice = $participation->getMeal()->getPrice()->getPriceValue();
            if ($participation->getMeal()->isCombinedMeal()) {
                $mealPrice = $participation->getMeal()->getPrice()->getPriceCombinedValue();
            }
            $price += $mealPrice;
        }

        return $price;
    }
}
