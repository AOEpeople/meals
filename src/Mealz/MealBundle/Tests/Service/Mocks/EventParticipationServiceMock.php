<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service\Mocks;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Service\EventParticipationServiceInterface;
use App\Mealz\UserBundle\Entity\Profile;
use Override;

final class EventParticipationServiceMock implements EventParticipationServiceInterface
{
    public Day $inputHandleDay;
    public EventParticipation $inputHandleEvent;
    public ?array $outputGetEventParticipationData = null;
    public Day $inputGetEventDataDay;
    public ?int $inputGetEventDataEventId = null;
    public ?Profile $inputGetEventDataProfile = null;
    public ?EventParticipation $outputJoin = null;
    public Profile $inputJoinProfile;
    public Day $inputJoinDay;
    public int $inputJoinEventId;
    public EventParticipation $outputJoinAsGuest;
    public string $inputJoinAsGuestFirstName;
    public string $inputJoinAsGuestLastName;
    public string $inputJoinAsGuestCompany;
    public Day $inputJoinAsGuestDay;
    public EventParticipation $inputJoinAsGuestEventParticipation;
    public ?EventParticipation $outputLeave = null;
    public Profile $inputLeaveProfile;
    public Day $inputLeaveDay;
    public int $inputLeaveEventId;
    public array $outputGetParticipants = [];
    public Day $inputGetParticipantsDay;
    public int $inputGetParticipantsEventId;

    #[Override]
    public function handleEventParticipation(Day $day, EventParticipation $event): void
    {
        $this->inputHandleDay = $day;
        $this->inputHandleEvent = $event;
    }

    #[Override]
    public function getEventParticipationData(Day $day, ?int $eventId = null, ?Profile $profile = null): ?array
    {
        $this->inputGetEventDataDay = $day;
        $this->inputGetEventDataEventId = $eventId;
        $this->inputGetEventDataProfile = $profile;

        return $this->outputGetEventParticipationData;
    }

    #[Override]
    public function join(Profile $profile, Day $day, int $eventId): ?EventParticipation
    {
        $this->inputJoinProfile = $profile;
        $this->inputJoinDay = $day;
        $this->inputJoinEventId = $eventId;

        return $this->outputJoin;
    }

    #[Override]
    public function joinAsGuest(
        string $firstName,
        string $lastName,
        string $company,
        Day $eventDay,
        EventParticipation $eventParticipation
    ): EventParticipation {
        $this->inputJoinAsGuestFirstName = $firstName;
        $this->inputJoinAsGuestLastName = $lastName;
        $this->inputJoinAsGuestCompany = $company;
        $this->inputJoinAsGuestDay = $eventDay;
        $this->inputJoinAsGuestEventParticipation = $eventParticipation;

        return $this->outputJoinAsGuest;
    }

    #[Override]
    public function leave(Profile $profile, Day $day, int $eventId): ?EventParticipation
    {
        $this->inputLeaveProfile = $profile;
        $this->inputLeaveDay = $day;
        $this->inputLeaveEventId = $eventId;

        return $this->outputLeave;
    }

    #[Override]
    public function getParticipants(Day $day, int $eventId): array
    {
        $this->inputGetParticipantsDay = $day;
        $this->inputGetParticipantsEventId = $eventId;

        return $this->outputGetParticipants;
    }
}
