<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event when all offers for a meal are gone.
 */
final class MealOfferCancelledEvent extends Event
{
    private Participant $participant;

    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }

    public function getMeal(): ?Meal
    {
        return $this->participant->getMeal();
    }

    public function getParticipant(): Participant
    {
        return $this->participant;
    }
}
