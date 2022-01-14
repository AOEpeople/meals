<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\DayRepository;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Entity\SlotRepository;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class ParticipationService
{
    use ParticipationServiceTrait;

    private EntityManagerInterface $em;
    private Doorman $doorman;
    private ParticipantRepository $participantRepo;
    private SlotRepository $slotRepo;
    private DayRepository $dayRepo;

    public function __construct(
        EntityManagerInterface $em,
        Doorman $doorman,
        ParticipantRepository $participantRepo,
        SlotRepository $slotRepo,
        DayRepository $dayRepo
    ) {
        $this->em = $em;
        $this->doorman = $doorman;
        $this->participantRepo = $participantRepo;
        $this->slotRepo = $slotRepo;
        $this->dayRepo = $dayRepo;
    }

    /**
     * @psalm-return array{participant: Participant, offerer: Profile|null}|null
     */
    public function join(Profile $profile, Meal $meal, ?Slot $slot = null, array $dishSlugs = []): ?array
    {
        // user is attempting to take over an already booked meal by some participant
        if ($this->mealIsOffered($meal) && $this->allowedToAccept($meal)) {
            return $this->reassignOfferedMeal($meal, $profile, $dishSlugs);
        }

        // self joining by user, or adding by a kitchen staff
        if ($this->doorman->isUserAllowedToJoin($meal, $dishSlugs) || $this->doorman->isKitchenStaff()) {
            if ((null === $slot) || !$this->slotIsAvailable($slot, $meal->getDateTime())) {
                $slot = $this->getNextFreeSlot($meal->getDateTime());
            }

            $participant = $this->create($profile, $meal, $slot, $dishSlugs);

            return ['participant' => $participant, 'offerer' => null];
        }

        return null;
    }

    public function updateSlot(Profile $profile, DateTime $date, Slot $slot): void
    {
        if (!$this->slotIsAvailable($slot, $date)) {
            $slot = $this->getNextFreeSlot($date);
        }

        $this->participantRepo->updateSlot($profile, $date, $slot);
    }

    /**
     * Reassigns $meal - offered by a participant - to $profile.
     *
     * @psalm-return array{participant: Participant, offerer: Profile}|null
     */
    private function reassignOfferedMeal(Meal $meal, Profile $profile, array $dishSlugs = []): ?array
    {
        $participant = $this->getNextOfferingParticipant($meal, $dishSlugs);
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
    private function create(Profile $profile, Meal $meal, ?Slot $slot = null, array $dishSlugs = []): ?Participant
    {
        $participant = $this->createParticipation($profile, $meal, $slot, $dishSlugs);

        $this->em->persist($participant);
        $this->em->flush();

        $this->em->refresh($meal);

        return $participant;
    }

    /**
     * Checks if it's still possible (not too late) to accept an offered meal.
     */
    private function allowedToAccept(Meal $meal): bool
    {
        $now = new DateTime();
        $mealDay = $meal->getDay();

        return ($mealDay->getLockParticipationDateTime() < $now) && ($mealDay->getDateTime() > $now);
    }

    private function getNextOfferingParticipant(Meal $meal, array $dishSlugs = []): ?Participant
    {
        $this->em->refresh($meal);
        $flippedDishSlugs = array_flip($dishSlugs);

        /** @var Participant $participant */
        foreach ($meal->getParticipants() as $participant) {
            if (true === $participant->isPending()) {
                if (empty($flippedDishSlugs)) {
                    return $participant;
                }

                $combinedDishes = $participant->getCombinedDishes();
                if (count($combinedDishes) !== count($flippedDishSlugs)) {
                    continue;
                }

                $combinationFound = true;
                /** @var Dish $dish */
                foreach($combinedDishes as $dish) {
                    if (!isset($flippedDishSlugs[$dish->getSlug()])) {
                        $combinationFound = false;
                        break;
                    }
                }

                if ($combinationFound) {
                    return $participant;
                }
            }
        }

        return null;
    }

    /**
     * @psalm-return list<array{date: string, slot: string, booked: int, booked_by_user: bool}>
     */
    public function getSlotsStatusFor(Profile $profile): array
    {
        $fromDate = new DateTime('now');
        $toDate = new DateTime('friday next week 23:59:59');
        $openMealDaysSlots = $this->getOpenMealDaysWithSlots($fromDate, $toDate);
        $slotCountProvider = $this->getBookedSlotCountProvider($fromDate, $toDate);

        $slotsStatus = [];

        foreach ($openMealDaysSlots as $idx => $item) {
            $slotsStatus[$idx] = [
                'date' => $item['date']->format('Y-m-d'),
                'slot' => $item['slot']->getSlug(),
                'booked' => $slotCountProvider($item['date'], $item['slot']),
                'booked_by_user' => false,
            ];
        }

        $userParticipation = $this->participantRepo->getParticipantsOnDays($fromDate, $toDate, $profile);

        foreach ($userParticipation as $participation) {
            $slot = $participation->getSlot();
            if (null === $slot || $slot->isDisabled() || $slot->isDeleted()) {
                continue;
            }

            $k = $participation->getMeal()->getDateTime()->format('Y-m-d-') . $slot->getId();
            if (!isset($slotsStatus[$k])) {
                continue;   // skip, not an open participation
            }

            $slotsStatus[$k]['booked_by_user'] = isset($slotsStatus[$k]);
        }

        return array_values($slotsStatus);
    }

    /**
     * @psalm-return list<array{date: string, slot: string, booked: int, booked_by_user: bool}>
     */
    public function getSlotsStatusOn(DateTime $date): array
    {
        $startDate = (clone $date)->setTime(0, 0);
        $endDate = (clone $date)->setTime(23, 59, 59);
        $openMealDaysSlots = $this->getOpenMealDaysWithSlots($startDate, $endDate);
        $slotCountProvider = $this->getBookedSlotCountProvider($startDate, $endDate);

        $slotsStatus = [];

        foreach ($openMealDaysSlots as $idx => $item) {
            $slotsStatus[$idx] = [
                'date' => $item['date']->format('Y-m-d'),
                'slot' => $item['slot']->getSlug(),
                'booked' => $slotCountProvider($item['date'], $item['slot']),
                'booked_by_user' => false,
            ];
        }

        return array_values($slotsStatus);
    }

    /**
     * Get slots for each open (not expired) meal day up to a given date in the future.
     *
     * @return array An array of arrays, each containing date and related slot.
     *               Top level array items are indexed by a composite key composed of date and slot-ID, i.e. Y-m-d-slot_id.
     *
     * @psalm-return array<string, array{date: DateTime, slot: Slot}>
     */
    private function getOpenMealDaysWithSlots(DateTime $stateDate, DateTime $endDate): array
    {
        $daysWithSlots = [];
        $mealDays = $this->dayRepo->findAllActive($stateDate, $endDate);
        $mealSlots = $this->slotRepo->findBy(['disabled' => 0, 'deleted' => 0], ['order' => 'ASC']);

        foreach ($mealDays as $day) {
            foreach ($mealSlots as $slot) {
                $date = $day->getDateTime();
                $k = $date->format('Y-m-d') . '-' . $slot->getId();
                $daysWithSlots[$k] = [
                    'date' => $date,
                    'slot' => $slot,
                ];
            }
        }

        return $daysWithSlots;
    }

    /**
     * Get status of booked slots from $startDate to $endDate.
     *
     * The return results are indexed by a composite key comprised of concatenated date and slot-ID.
     */
    private function getBookedSlotCountProvider(DateTime $startDate, DateTime $endDate): callable
    {
        $slotBookingStatus = [];
        $bookedSlotsStatus = $this->participantRepo->getCountBySlots($startDate, $endDate);

        foreach ($bookedSlotsStatus as $bss) {
            $k = $bss['date']->format('Y-m-d-') . $bss['slot'];
            $slotBookingStatus[$k] = $bss['count'];
        }

        return static function (DateTime $date, Slot $slot) use ($slotBookingStatus): int {
            $k = $date->format('Y-m-d-') . $slot->getId();

            return $slotBookingStatus[$k] ?? 0;
        };
    }

    /**
     * Gets count of offered meals on $date.
     */
    public function getOfferCount(DateTime $date): int
    {
        return $this->participantRepo->getOfferCount($date);
    }

    public function getSlot(Profile $profile, DateTime $date): ?Slot
    {
        $startDate = (clone $date)->setTime(0, 0);
        $endDate = (clone $date)->setTime(23, 59, 59);
        $participants = $this->participantRepo->getParticipantsOnDays($startDate, $endDate, $profile);

        foreach ($participants as $participant) {
            $slot = $participant->getSlot();
            if (null !== $slot) {
                return $slot;
            }
        }

        return null;
    }

    public function getBookedDishCombination(Profile $profile, Meal $meal): Collection
    {
        $participant = $meal->getParticipant($profile);
        if (null === $participant) {
            return new ArrayCollection();
        }

        return $participant->getCombinedDishes();
    }
}
