<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event when an offered meal is accepted by a user.
 */
final class MealOfferAcceptedEvent extends Event
{
    /**
     * Updated (new user) meal participant.
     */
    private Participant $participant;

    /**
     * User who offered the meal.
     */
    private Profile $offerer;

    public function __construct(Participant $participant, Profile $offerer)
    {
        $this->participant = $participant;
        $this->offerer = $offerer;
    }

    public function getMeal(): ?Meal
    {
        return $this->participant->getMeal();
    }

    public function getOfferer(): Profile
    {
        return $this->offerer;
    }

    public function getParticipant(): Participant
    {
        return $this->participant;
    }
}
