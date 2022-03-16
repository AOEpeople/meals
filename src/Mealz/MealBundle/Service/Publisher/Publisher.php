<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Publisher;

abstract class Publisher
{
    public const TOPIC_PARTICIPANT_COUNT = '/participant-update';
    public const TOPIC_MEAL_OFFERS = 'meal-offer-updates';
    public const TOPIC_SLOT_ALLOCATION_UPDATES = 'slot-allocation-updates';
}
