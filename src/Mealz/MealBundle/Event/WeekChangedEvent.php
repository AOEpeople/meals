<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event;

use App\Mealz\MealBundle\Entity\Week;
use Symfony\Contracts\EventDispatcher\Event;

class WeekChangedEvent extends Event
{
    private Week $week;

    public function __construct($week)
    {
        $this->week = $week;
    }

    public function getWeek(): Week
    {
        return $this->week;
    }
}