<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Publisher;

abstract class Publisher
{
    public const TOPIC_PARTICIPANT_COUNT = '/participant-update';
    public const TOPIC_UPDATE_OFFER      = '/offer-update';
    public const TOPIC_UPDATE_SLOT       = '/slot-update';
}