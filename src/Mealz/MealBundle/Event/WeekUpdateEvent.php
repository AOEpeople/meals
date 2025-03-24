<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event;

use App\Mealz\MealBundle\Entity\Week;
use Symfony\Contracts\EventDispatcher\Event;

final class WeekUpdateEvent extends Event
{
    private Week $week;
    private bool $notify;

    public function __construct($week, bool $notify = false)
    {
        $this->week = $week;
        $this->notify = $notify;
    }

    public function getWeek(): Week
    {
        return $this->week;
    }

    public function getNotify(): bool
    {
        return $this->notify;
    }
}
