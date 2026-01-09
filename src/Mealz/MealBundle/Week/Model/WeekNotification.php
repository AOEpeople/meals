<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Week\Model;

final readonly class WeekNotification
{
    public function __construct(
        public bool $shouldNotify
    ) {
    }
}
