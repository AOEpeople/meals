<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Entity\SlotRepository;
use App\Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class ParticipationService
{
    private EntityManagerInterface $em;
    private Doorman $doorman;
    private ParticipantRepository $participantRepo;
    private SlotRepository $slotRepo;

    public function __construct(
        EntityManagerInterface $em,
        Doorman $doorman,
        ParticipantRepository $participantRepo,
        SlotRepository $slotRepo
    )
    {
        $this->em = $em;
        $this->doorman = $doorman;
        $this->participantRepo = $participantRepo;
        $this->slotRepo = $slotRepo;
    }

    public function join(Profile $profile, Meal $meal, $slot = null): ?array
    {
        if ($this->doorman->loggedInKitchenStaff($profile)) {
            return null; // $profile - a kitchen staff - adding itself to $meal; return null
        }

        // self joining by user, or adding by a kitchen staff
        if ($this->doorman->isUserAllowedToJoin($meal) || $this->doorman->isKitchenStaff()) {
            $slot = $slot ?? $this->getNextFreeSlot($meal);
            $participant = $this->create($profile, $meal, $slot);

            return ['participant' => $participant, 'offerer' => null];
        }

        // user is attempting to take over an already booked meal by some participant
        if ($this->mealIsOffered($meal) && $this->allowedToAccept($meal)) {
            return $this->reassignOfferedMeal($meal, $profile);
        }

        return null;
    }

    /**
     * Reassigns $meal - offered by a participant - to $profile.
     *
     * @return array{participant: Participant, offerer: Profile}
     */
    private function reassignOfferedMeal(Meal $meal, Profile $profile): ?array
    {
        $participant = $this->getNextOfferingParticipant($meal);
        if (null === $participant) {
            return null;
        }

        $offerer = $participant->getProfile();

        $participant->setProfile($profile);
        $participant->setOfferedAt(0);

        $this->em->persist($participant);
        $this->em->flush();

        return ['participant' => $participant, 'offerer' => $offerer];
    }

    /**
     * Creates a new participation for user $profile in meal $meal in slot $slot.
     */
    private function create(Profile $profile, Meal $meal, ?Slot $slot): ?Participant
    {
        $this->em->beginTransaction();
        $participant = new Participant($profile, $meal, $slot);

        try {
            $this->em->persist($participant);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ParticipantNotUniqueException $e) {
            $this->em->getConnection()->rollBack();

            return null;
        }

        $this->em->refresh($meal);

        return $participant;
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

    /**
     * Checks if it's still possible (not too late) to accept an offered meal.
     */
    private function allowedToAccept(Meal $meal): bool
    {
        $now = new DateTime();
        $mealDay = $meal->getDay();

        return (($mealDay->getLockParticipationDateTime() < $now) && ($mealDay->getDateTime() > $now));
    }

    private function getNextOfferingParticipant(Meal $meal): ?Participant
    {
        $this->em->refresh($meal);

        /** @var Participant $participant */
        foreach ($meal->getParticipants() as $participant) {
            if (true === $participant->isPending()) {
                return $participant;
            }
        }

        return null;
    }

    public function getNextFreeSlot(Meal $meal): ?Slot
    {
        $slots = $this->slotRepo->findBy(['disabled' => 0], ['order' => 'ASC']);
        if (1 > count($slots)) {
            return null; // no active slots are available; return null
        }

        $countBySlots = $this->participantRepo->getCountBySlots($meal->getDateTime());
        if (0 === count($countBySlots)) {
            return $slots[0]; // no participants yet; return first slot
        }

        foreach ($slots as $slot) {
            $slotID = $slot->getId();
            if (!isset($countBySlots[$slotID])) {
                return $slot; // $slot is not at all booked; return it
            }

            $slotLimit = $slot->getLimit();
            if (0 === $slotLimit || $slotLimit > $countBySlots[$slotID]) {
                return $slot; // $slot has either no limit (zero), or has bookings less than its limit; return it
            }
        }

        return null;
    }

    /**
     * Gets count of offered meals for $meal.
     */
    public function getOfferCount(DateTime $date): int
    {
        return $this->participantRepo->getOfferCount($date);
    }
}
