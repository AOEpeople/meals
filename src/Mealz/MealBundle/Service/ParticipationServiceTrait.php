<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Entity\SlotRepository;
use DateTime;

/**
 * @property ParticipantRepository $participantRepo
 * @property SlotRepository        $slotRepo
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
            if (!isset($indexedSlotsPart[$slotID])) {
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
     * Checks if the given meal is bookable, i.e. not expired, locked or not fully booked.
     */
    private function mealIsBookable(Meal $meal): bool
    {
        $now = new DateTime();

        // meal date-time is in the past; not bookable
        if ($meal->getDateTime() <= $now) {
            return false;
        }

        // meal date-time is not in the past, but meal is locked and nobody is offering; not bookable
        if (($meal->getLockDateTime() <= $now) && !$this->mealIsOffered($meal)) {
            return false;
        }

        // meal is open and count of booked meals is below the meal limit; bookable
        return !$this->mealLimitReached($meal);
    }

    /**
     * Checks if the number of bookings reached the meal limit.
     */
    private function mealLimitReached(Meal $meal): bool
    {
        $mealLimit = $meal->getParticipationLimit();
        if (1 > $mealLimit) {
            return false;
        }

        $bookedMealCount = $this->participantRepo->getBookedMealCount($meal);

        return $bookedMealCount < $mealLimit;
    }

    /**
     * Checks if any participant is offering its meal.
     */
    private function mealIsOffered(Meal $meal): bool
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
}
