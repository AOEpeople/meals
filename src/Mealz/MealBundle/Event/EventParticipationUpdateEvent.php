<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event;

use App\Mealz\MealBundle\Entity\EventParticipation;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that is triggert when a event participation is updated.
 */

final class EventParticipationUpdateEvent extends Event
{
    private EventParticipation $eventParticpation;

    public function __construct(EventParticipation $eventParticipation)
    {
        $this->eventParticpation = $eventParticipation;
    }

    public function getEventParticipation(): EventParticipation
    {
        return $this->eventParticpation;
    }
}
