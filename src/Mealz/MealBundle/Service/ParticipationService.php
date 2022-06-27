<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\DayRepository;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepository;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class ParticipationService extends BaseController
{
    use ParticipationServiceTrait;

    private EntityManagerInterface $em;
    private Doorman $doorman;

    private DayRepository $dayRepo;
    private ParticipantRepositoryInterface $participantRepo;
    private SlotRepository $slotRepo;

    public function __construct(
        EntityManagerInterface $em,
        Doorman $doorman,
        DayRepository $dayRepo,
        ParticipantRepositoryInterface $participantRepo,
        SlotRepository $slotRepo
    ) {
        $this->em = $em;
        $this->doorman = $doorman;
        $this->dayRepo = $dayRepo;
        $this->participantRepo = $participantRepo;
        $this->slotRepo = $slotRepo;
    }

    /**
     * @psalm-return array{participant: Participant, offerer: Profile|null}|null
     *
     * @throws ParticipationException
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

    /**
     * Update combined meal dishes for a participant.
     *
     * @param string[] $dishSlugs
     *
     * @throws ParticipationException
     */
    public function updateCombinedMeal(Participant $participant, array $dishSlugs): void
    {
        $meal = $participant->getMeal();

        if (!$meal->isOpen()) {
            throw new ParticipationException(
                'invalid operation; meal expired',
                ParticipationException::ERR_PARTICIPATION_EXPIRED
            );
        }
        if ($meal->isLocked()) {
            throw new ParticipationException(
                'invalid operation; participation is locked',
                ParticipationException::ERR_UPDATE_LOCKED_PARTICIPATION
            );
        }

        $this->updateCombinedMealDishes($participant, $dishSlugs);

        $this->em->persist($participant);
        $this->em->flush();
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
     *
     * @throws ParticipationException
     */
    private function create(Profile $profile, Meal $meal, ?Slot $slot = null, array $dishSlugs = []): ?Participant
    {
        $participant = $this->createParticipation($profile, $meal, $slot, $dishSlugs);

        $this->em->persist($participant);
        $this->em->flush();

        $meal->participants->add($participant);

        return $participant;
    }

    /**
     * Checks if it's still possible (not too late) to accept an offered meal.
     */
    private function allowedToAccept(Meal $meal): bool
    {
        return $meal->isOpen() && $meal->isLocked();
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
                foreach ($combinedDishes as $dish) {
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
     * @psalm-return array<string, int> Key-value pair of slot-slug and related allocation count
     */
    public function getSlotsStatusOn(DateTime $date): array
    {
        $startDate = (clone $date)->setTime(0, 0);
        $endDate = (clone $date)->setTime(23, 59, 59);
        $openMealDaysSlots = $this->getOpenMealDaysWithSlots($startDate, $endDate);
        $slotCountProvider = $this->getBookedSlotCountProvider($startDate, $endDate);

        $slotsStatus = [];

        foreach ($openMealDaysSlots as $item) {
            $slotSlug = $item['slot']->getSlug();
            $allocationCount = $slotCountProvider($item['date'], $item['slot']);
            $slotsStatus[$slotSlug] = $allocationCount;
        }

        return $slotsStatus;
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

    public function getCountByMeal(Meal $meal): int
    {
        $participation = ParticipationCountService::getParticipationByDay($meal->getDay());

        if ($meal->isCombinedMeal()) {
            return $participation['countByMealIds'][$meal->getId()][$meal->getDish()->getSlug()]['count'] ?? 0;
        }

        return (int) ceil($participation['totalCountByDishSlugs'][$meal->getDish()->getSlug()]['count'] ?? 0);
    }

    public function isUserParticipating(Meal $meal): bool
    {
        $profile = $this->getUser()->getProfile();

        /* @var Participant $participant */
        foreach ($meal->getParticipants() as $participant) {
            if($participant->getProfile() === $profile) {

                return true;
            }
        }

        return false;
    }
}
