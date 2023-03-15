<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\Persistence\ObjectRepository;

interface ParticipantRepositoryInterface extends ObjectRepository
{
    /**
     * @return Participant[]
     */
    public function getParticipantsOnDays(DateTime $startDate, DateTime $endDate, Profile $profile = null): array;

    public function getTotalCost(string $username): float;

    /**
     * @return Participant[]
     */
    public function getLastAccountableParticipations(Profile $profile, ?int $limit = null): array;

    public function findCostsGroupedByUserGroupedByMonth(): array;

    /**
     * @param Participant[] $participants
     *
     * @psalm-return array<string, list<Participant>>
     */
    public function groupParticipantsByName(array $participants): array;

    /**
     * @psalm-return array<string, array<string, array<string, bool>>>
     */
    public function findAllGroupedBySlotAndProfileID(DateTime $date): array;

    /**
     * @return Participant[]
     */
    public function getParticipantsOnCurrentDay(array $options = []): array;

    /**
     * Returns count of booked meals available to be taken by others on a given $date.
     */
    public function getOfferCount(DateTime $date): int;

    /**
     * Returns count of booked meals available to be taken over by others.
     */
    public function getOfferCountByMeal(Meal $meal): int;

    /**
     * Returns true if the specified user is offering the specified meal.
     */
    public function isOfferingMeal(Profile $profile, Meal $meal): bool;

    /**
     * Gets number of participants (booked meals) per slot on a given $date.
     *
     * @psalm-return list<array{date: DateTime, slot: int, count: int}>
     */
    public function getCountBySlots(DateTime $startDate, DateTime $endDate): array;

    /**
     * Gets number of participants booked for a slot on a given day.
     */
    public function getCountBySlot(Slot $slot, DateTime $date): int;

    /**
     * Gets number of participants booked for a certain meal.
     */
    public function getCountByMeal(Meal $meal): int;

    public function updateSlot(Profile $profile, DateTime $date, Slot $slot): void;

    /**
     * Removes all future ordered meals for a given profile.
     */
    public function removeFutureMealsByProfile(Profile $profile): void;
}
