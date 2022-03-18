<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Publisher;

abstract class Publisher
{
    public const TOPIC_PARTICIPATION_UPDATES = 'participation-updates';
    public const TOPIC_MEAL_OFFER_UPDATES = 'meal-offer-updates';
    public const TOPIC_SLOT_ALLOCATION_UPDATES = 'slot-allocation-updates';
}
