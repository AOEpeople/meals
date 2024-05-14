<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepositoryInterface;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class ParticipationService
{
    use ParticipationServiceTrait;

    private EntityManagerInterface $em;
    private Doorman $doorman;

    private DayRepositoryInterface $dayRepo;
    private ParticipantRepositoryInterface $participantRepo;
    private SlotRepositoryInterface $slotRepo;

    public function __construct(
        EntityManagerInterface $em,
        Doorman $doorman,
        DayRepositoryInterface $dayRepo,
        ParticipantRepositoryInterface $participantRepo,
        SlotRepositoryInterface $slotRepo
    ) {
        $this->em = $em;
        $this->doorman = $doorman;
        $this->dayRepo = $dayRepo;
        $this->participantRepo = $participantRepo;
        $this->slotRepo = $slotRepo;
    }

    /**
     * @psalm-return array{participant: Participant, offerer: Profile|null, slot: Slot|null}|null
     *
     * @throws ParticipationException
     */
    public function join(Profile $profile, Meal $meal, ?Slot $slot = null, array $dishSlugs = []): ?array
    {
        // user is attempting to take over an already booked meal by some participant
        if (true === $this->mealIsOffered($meal) && true === $this->allowedToAccept($meal)) {
            return $this->reassignOfferedMeal($meal, $profile, $dishSlugs);
        }

        // self joining by user, or adding by a kitchen staff
        if ($this->doorman->isUserAllowedToJoin($meal, $dishSlugs) || $this->doorman->isKitchenStaff()) {
            if ((null === $slot) || false === $this->slotIsAvailable($slot, $meal->getDateTime())) {
                $slot = $this->getNextFreeSlot($meal->getDateTime());
            }

            $participant = $this->create($profile, $meal, $slot, $dishSlugs);

            return ['participant' => $participant, 'offerer' => null, 'slot' => $slot];
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

        if (false === $meal->isOpen()) {
            throw new ParticipationException(
                'invalid operation; meal expired',
                ParticipationException::ERR_PARTICIPATION_EXPIRED
            );
        }
        if (true === $meal->isLocked()) {
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
        if (false === $this->slotIsAvailable($slot, $date)) {
            $slot = $this->getNextFreeSlot($date);
        }

        $this->participantRepo->updateSlot($profile, $date, $slot);
    }

    /**
     * Reassigns $meal - offered by a participant - to $profile.
     *
     * @psalm-return array{participant: Participant, offerer: Profile, slot: Slot|null}|null
     */
    private function reassignOfferedMeal(Meal $meal, Profile $profile, array $dishSlugs = []): ?array
    {
        $participant = $this->getNextOfferingParticipant($meal, $dishSlugs);
        if (null === $participant) {
            return null;
        }

        $offerer = $participant->getProfile();

        $slot = $participant->getSlot();

        $participant->setProfile($profile);
        $participant->setOfferedAt(0);

        $this->em->persist($participant);
        $this->em->flush();

        return ['participant' => $participant, 'offerer' => $offerer, 'slot' => $slot];
    }

    /**
     * Creates a new participation for user $profile in meal $meal in slot $slot.
     *
     * @throws ParticipationException
     */
    private function create(Profile $profile, Meal $meal, ?Slot $slot = null, array $dishSlugs = []): ?Participant
    {
        $participant = $this->createParticipation($profile, $meal, $slot, $dishSlugs);
        $meal->participants->add($participant);

        $this->em->persist($participant);
        $this->em->flush();

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
                if (1 === count($flippedDishSlugs)) {
                    return $participant;
                }

                $combinedDishes = $participant->getCombinedDishes();
                if (count($combinedDishes) !== count($flippedDishSlugs)) {
                    continue;
                }

                $combinationFound = true;
                /** @var Dish $dish */
                foreach ($combinedDishes as $dish) {
                    if (false === isset($flippedDishSlugs[$dish->getSlug()])) {
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

    public function getParticipationByMealAndUser(Meal $meal, Profile $profile): ?Participant
    {
        $participants = $meal->getParticipants();

        foreach ($participants as $participant) {
            if ($participant->getProfile()->getUsername() === $profile->getUsername()) {
                return $participant;
            }
        }

        return null;
    }

    public function getCountOfActiveParticipationsByDayAndUser(DateTime $dateTime, Profile $profile): int
    {
        $activeParticipations = $this->participantRepo->getParticipantsOnDays(
            $dateTime,
            $dateTime,
            $profile
        );

        return count($activeParticipations);
    }

    public function getSlot(Profile $profile, DateTime $date): ?Slot
    {
        $startDate = (clone $date)->setTime(0, 0);
        $endDate = (clone $date)->setTime(23, 59, 59);
        $participants = $this->participantRepo->getParticipantsOnDays($startDate, $endDate, $profile);

        foreach ($participants as $participant) {
            $slot = $participant->getSlot();
            if (null !== $slot && false === $slot->isDisabled()) {
                return $slot;
            }
        }

        return null;
    }

    public function getCountByMeal(Meal $meal, bool $withoutCombined = false): int
    {
        $participation = (new ParticipationCountService())->getParticipationByDay($meal->getDay());

        if (true === $meal->isCombinedMeal() || true === $withoutCombined) {
            return $participation['countByMealIds'][$meal->getId()][$meal->getDish()->getSlug()]['count'] ?? 0;
        }

        return (int) ceil($participation['totalCountByDishSlugs'][$meal->getDish()->getSlug()]['count'] ?? 0);
    }

    public function getParticipationList(Day $day): array
    {
        return $this->participantRepo->getParticipantsByDay($day->getDateTime(), ['load_meal' => false]);
    }

    public function getParticipationListBySlots(Day $day, bool $getProfile = false): array
    {
        return $this->participantRepo->findAllGroupedBySlotAndProfileID($day->getDateTime(), $getProfile);
    }

    public function getDishesByDayAndProfile(Day $day, Profile $profile): array
    {
        $meals = $day->getMeals();
        $dishesOfProfile = [];

        /** @var Meal $meal */
        foreach ($meals as $meal) {
            if (null !== $meal->getParticipant($profile)) {
                $dishesOfProfile[] = $meal->getDish()->getId();
            }
        }

        return $dishesOfProfile;
    }

    public function getMealsForTheDay(Day $day): MealCollection
    {
        $result = new MealCollection();

        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            $result->add($meal);
        }

        return $result;
    }

    public function getParticipationsByDayAndProfile(Profile $profile, Day $day): array
    {
        $result = [];
        $meals = $day->getMeals();

        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $participation = $this->getParticipationByMealAndUser($meal, $profile);
            if (null !== $participation) {
                $result[] = $participation;
            }
        }

        return $result;
    }

    public function setParticipationSlotsEmpty(array $participations): void
    {
        /** @var Participant $participation */
        foreach ($participations as $participation) {
            $participation->setSlot(null);
            $this->em->persist($participation);
        }

        $this->em->flush();
    }
}
