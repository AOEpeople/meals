<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Helper;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Repository\EventPartRepo;
use App\Mealz\MealBundle\Repository\EventRepository;
use App\Mealz\MealBundle\Service\EventParticipationService;

final class MealAdminHelper
{
    public function __construct(
        private readonly EventParticipationService $eventService,
        private readonly EventRepository $eventRepository,
        private readonly EventPartRepo $eventPartRepo
    ) {
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

    public function findEvent(int $eventId): Event
    {
        return $this->eventRepository->find($eventId);
    }

    public function checkIfEventExistsForDay(int $eventId, Day $day): bool
    {
        if ($this->eventPartRepo->findByEventIdAndDay($day, $eventId)) {
            return true;
        }

        return false;
    }
}
