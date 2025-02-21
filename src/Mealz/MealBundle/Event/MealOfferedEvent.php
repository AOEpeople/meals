<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use Symfony\Contracts\EventDispatcher\Event;

final class MealOfferedEvent extends Event
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
