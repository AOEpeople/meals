<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event;

use App\Mealz\MealBundle\Entity\Meal;
use Symfony\Contracts\EventDispatcher\Event;

class MealOfferedEvent extends Event
{
    private Meal $Meal;

    public function __construct(Meal $meal)
    {
        $this->Meal = $meal;
    }

    public function getMeal(): Meal
    {
        return $this->Meal;
    }
}
