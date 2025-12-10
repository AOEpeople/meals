<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\UserBundle\Entity\Profile;

interface EventParticipationServiceInterface
{
    public function handleEventParticipation(Day $day, EventParticipation $event): void;
    public function getEventParticipationData(Day $day, ?int $eventId = null, ?Profile $profile = null): ?array;
    public function join(Profile $profile, Day $day, int $eventId): ?EventParticipation;
    public function joinAsGuest(
        string $firstName,
        string $lastName,
        string $company,
        Day $eventDay,
        EventParticipation $eventParticipation,
    ): EventParticipation;
    public function leave(Profile $profile, Day $day, int $eventId): ?EventParticipation;
    public function getParticipants(Day $day, int $eventId): array;
}