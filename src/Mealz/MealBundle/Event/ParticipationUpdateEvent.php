<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event;

use App\Mealz\MealBundle\Entity\Participant;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that is triggered when a meal participation is updated, i.e. a meal is booked, canceled, or updated.
 */
final class ParticipationUpdateEvent extends Event
{
    private Participant $participant;

    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }

    public function getParticipant(): Participant
    {
        return $this->participant;
    }
}
