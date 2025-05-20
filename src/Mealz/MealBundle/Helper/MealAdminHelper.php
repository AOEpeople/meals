<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Helper;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Service\EventParticipationService;

final class MealAdminHelper
{
    private EventParticipationService $eventService;

    public function __construct(
        EventParticipationService $eventService
    ) {
        $this->eventService = $eventService;
    }

    public function setParticipationLimit(Meal $mealEntity, array $meal): void
    {
        if (
            true === isset($meal['participationLimit'])
            && 0 < $meal['participationLimit']
            && count($mealEntity->getParticipants()) <= $meal['participationLimit']
        ) {
            $mealEntity->setParticipationLimit($meal['participationLimit']);
        } else {
            $mealEntity->setParticipationLimit(0);
        }
    }

    public function handleEventParticipation(Day $day, ?int $eventId = null): void
    {
        $this->eventService->handleEventParticipation($day, $eventId);
    }
}
