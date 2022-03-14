<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event;

use App\Mealz\MealBundle\Entity\Meal;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event when all offers for a meal are gone.
 */
class MealOfferCancelledEvent extends Event
{
    private Meal $meal;

    public function __construct(Meal $meal)
    {
        $this->meal = $meal;
    }

    public function getMeal(): Meal
    {
        return $this->meal;
    }
}
