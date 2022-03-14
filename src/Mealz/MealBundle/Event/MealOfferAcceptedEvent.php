<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event when an offered meal is accepted by a user.
 */
class MealOfferAcceptedEvent extends Event
{
    /**
     * Previous participant who offered the meal.
     */
    private Participant $offerer;

    public function __construct(Participant $offerer)
    {
        $this->offerer = $offerer;
    }

    public function getMeal(): Meal
    {
        return $this->offerer->getMeal();
    }

    public function getOfferer(): Participant
    {
        return $this->offerer;
    }
}
