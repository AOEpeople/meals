<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepositoryInterface;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;

/**
 * @property ParticipantRepositoryInterface $participantRepo
 * @property SlotRepositoryInterface        $slotRepo
 */
trait ParticipationServiceTrait
{
    private function getNextFreeSlot(DateTime $mealDate): ?Slot
    {
        $slots = $this->slotRepo->findBy(['disabled' => 0, 'deleted' => 0], ['order' => 'ASC']);
        if (1 > count($slots)) {    // no active slots are available
            return null;
        }

        $slotsPart = $this->participantRepo->getCountBySlots($mealDate, $mealDate);
        if (0 === count($slotsPart)) {  // no participants yet
            return $slots[0];
        }

        // index slot count items by slot-ID
        $indexedSlotsPart = [];
        foreach ($slotsPart as $sp) {
            $indexedSlotsPart[$sp['slot']] = $sp;
        }

        foreach ($slots as $slot) {
            $slotID = $slot->getId();
            if (false === isset($indexedSlotsPart[$slotID])) {
                return $slot; // $slot is not at all booked; return it
            }

            $slotLimit = $slot->getLimit();
            if (0 === $slotLimit || $slotLimit > $indexedSlotsPart[$slotID]['count']) {
                return $slot; // $slot has either no limit (zero), or has bookings less than its limit; return it
            }
        }

        return null;
    }

    /**
     * Checks if the given meal is bookable, i.e. not expired or locked but offered. Doesn't check if limit is reached!
     */
    private function mealIsBookable(Meal $meal): bool
    {
        $now = new DateTime();

        // meal date-time is in the past; not bookable
        if ($meal->getDateTime() <= $now) {
            return false;
        }

        // meal date-time is not in the past, but meal is locked and nobody is offering; not bookable
        if (($meal->getLockDateTime() <= $now) && false === $this->mealIsOffered($meal)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if any participant is offering its meal.
     */
    public function mealIsOffered(Meal $meal): bool
    {
        /** @var Participant $participant */
        foreach ($meal->getParticipants() as $participant) {
            if (true === $participant->isPending()) {
                return true;
            }
        }

        return false;
    }

    private function slotIsAvailable(Slot $slot, DateTime $date): bool
    {
        $slotLimit = $slot->getLimit();
        if (0 === $slotLimit) {
            return true;
        }

        $slotPartCount = $this->participantRepo->getCountBySlot($slot, $date);

        return $slotPartCount < $slotLimit;
    }

    /**
     * @throws ParticipationException
     */
    private function createParticipation(Profile $profile, Meal $meal, ?Slot $slot = null, array $dishSlugs = []): Participant
    {
        $participant = new Participant($profile, $meal);
        if (null !== $slot) {
            $participant->setSlot($slot);
        }

        if (true === $meal->isCombinedMeal()) {
            $this->updateCombinedMealDishes($participant, $dishSlugs);
        }

        return $participant;
    }

    /**
     * @throws ParticipationException
     */
    private function updateCombinedMealDishes(Participant $participant, array $dishSlugs): void
    {
        $meal = $participant->getMeal();

        if (false === $meal->isCombinedMeal()) {
            throw new ParticipationException(
                'invalid operation; normal meal participation cannot be updated',
                ParticipationException::ERR_INVALID_OPERATION
            );
        }
        if (2 !== count($dishSlugs)) {
            throw new ParticipationException(
                'invalid dish count; combined meal expects 2 dishes, got ' . count($dishSlugs),
                ParticipationException::ERR_COMBI_MEAL_INVALID_DISH_COUNT
            );
        }

        $dishes = $this->getCombinedMealDishes($meal, $dishSlugs);

        if (2 !== count($dishes)) {
            throw new ParticipationException(
                'invalid dish count; combined meal expects 2 dishes, got ' . count($dishes),
                ParticipationException::ERR_COMBI_MEAL_INVALID_DISH_COUNT
            );
        }

        $participant->setCombinedDishes($dishes);
    }

    /**
     * @return \App\Mealz\MealBundle\Entity\Dish[]
     *
     * @psalm-return list<\App\Mealz\MealBundle\Entity\Dish>
     */
    private function getCombinedMealDishes(Meal $meal, array $dishSlugs): array
    {
        $dishes = [];

        foreach ($meal->getDay()->getMeals() as $m) {
            $dish = $m->getDish();
            if (true === $dish->isCombinedDish() || false === in_array($dish->getSlug(), $dishSlugs, true)) {
                continue;
            }

            $dishes[] = $dish;
        }

        return $dishes;
    }
}
