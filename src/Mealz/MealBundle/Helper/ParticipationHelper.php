<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Helper;

use App\Mealz\MealBundle\Entity\DishCollection;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Service\ParticipationCountService;
use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;

class ParticipationHelper
{
    private ProfileRepositoryInterface $profileRepo;
    private ParticipationCountService $partCountSrv;

    public function __construct(
        ProfileRepositoryInterface $profileRepo,
        ParticipationCountService $partCountSrv
    ) {
        $this->profileRepo = $profileRepo;
        $this->partCountSrv = $partCountSrv;
    }

    /**
     * helper function to sort participants by their name or guest name.
     *
     * @return Participant[]
     *
     * @psalm-return list<Participant>
     */
    public function sortParticipantsByName($participants): array
    {
        usort($participants, [$this, 'compareNameOfParticipants']);

        return $participants;
    }

    /**
     * @param Participant[] $participants
     *
     * @psalm-return array<string, array<string, array{booked: non-empty-list<int>}>>
     */
    public function groupBySlotAndProfileID(array $participants, bool $getProfile = false): array
    {
        $groupedParticipants = [];

        foreach ($participants as $participant) {
            $slot = $participant->getSlot();

            if (null !== $slot && array_key_exists($slot->getTitle(), $groupedParticipants) && array_key_exists($participant->getProfile()->getFullName(), $groupedParticipants[$slot->getTitle()])) {
                continue;
            }

            $groupedParticipants = array_merge_recursive($groupedParticipants, $this->getParticipationbySlot($participant, $slot, $getProfile));
        }

        return $groupedParticipants;
    }

    /**
     * @return (string|string[])[][]
     *
     * @psalm-return array<array{user: string, fullName: string, roles: array<string>}>
     */
    public function getNonParticipatingProfilesByWeek(array $participations): array
    {
        $profiles = array_map(
            fn ($participant) => $participant->getProfile()->getUserName(),
            $participations
        );

        if (0 === count($profiles)) {
            $profiles = [''];
        }

        $nonParticipating = $this->profileRepo->findAllExcept($profiles);

        $profileData = array_map(
            fn ($profile) => [
                'user' => $profile->getUsername(),
                'fullName' => $profile->getFullName(),
                'roles' => $profile->getRoles(),
            ],
            $nonParticipating
        );

        return $profileData;
    }

    public function getReachedLimit(Meal $meal): bool
    {
        $participationsPerDay = $this->partCountSrv->getParticipationByDay($meal->getDay());
        $participationCount = null;
        if (array_key_exists($meal->getDish()->getSlug(), $participationsPerDay['totalCountByDishSlugs'])) {
            $participationCount = $participationsPerDay['totalCountByDishSlugs'][$meal->getDish()->getSlug()]['count'];
        } else {
            $participationCount = $meal->getParticipants()->count();
        }

        return $meal->getParticipationLimit() > 0.0 ? $participationCount >= $meal->getParticipationLimit() : false;
    }

    public function getParticipationMealData(Participant $participant): array
    {
        $participationData['mealId'] = $participant->getMeal()->getId();
        $participationData['dishId'] = $participant->getMeal()->getDish()->getId();
        $participationData['combinedDishes'] = array_map(
            fn ($dish) => $dish->getId(),
            $participant->getCombinedDishes()->toArray()
        );

        return $participationData;
    }

    public function getParticipantName(Participant $participant): string
    {
        $fullname = $participant->getProfile()->getFullName();
        if (true === $participant->getProfile()->isGuest()) {
            $fullname .= ' (Guest)';
        }

        return $fullname;
    }

    protected function compareNameOfParticipants(Participant $participant1, Participant $participant2): int
    {
        $result = strcasecmp($participant1->getProfile()->getName(), $participant2->getProfile()->getName());

        if (0 !== $result) {
            return $result;
        } elseif ($participant1->getMeal()->getDateTime() < $participant2->getMeal()->getDateTime()) {
            return 1;
        } elseif ($participant1->getMeal()->getDateTime() > $participant2->getMeal()->getDateTime()) {
            return -1;
        }

        return 0;
    }

    /**
     * @return array[][]
     *
     * @psalm-return array<string, array<string, array>>
     */
    private function getParticipationbySlot(Participant $participant, ?Slot $slot, bool $profile = false): array
    {
        $slots = [];

        /** @var Meal $meal */
        foreach ($participant->getMeal()->getDay()->getMeals() as $meal) {
            $combinedDishes = $this->getCombinedDishesFromMeal($meal, $participant);

            if (true === $meal->isParticipant($participant) && (null === $slot || $slot->isDisabled() || $slot->isDeleted())) {
                $slots[''][$this->getParticipantName($participant)] = $this->getParticipationData(
                    $meal,
                    $profile,
                    $participant,
                    $combinedDishes
                );
                continue;
            }

            if (true === $meal->isParticipant($participant)) {
                $slots[$slot->getTitle()][$this->getParticipantName($participant)] = $this->getParticipationData(
                    $meal,
                    $profile,
                    $participant,
                    $combinedDishes
                );
            }
        }

        return $slots;
    }

    private function getCombinedDishesFromMeal(Meal $meal, Participant $participant): DishCollection
    {
        if (true === $meal->isCombinedMeal() && true === $meal->isParticipant($participant)) {
            return $participant->getCombinedDishes();
        }

        return new DishCollection([]);
    }

    /**
     * @return ((bool|int|null)[]|string)[]
     *
     * @psalm-return array{booked: non-empty-list<int|null>, isOffering: non-empty-list<bool>, profile?: string}
     */
    private function getParticipationData(
        Meal $meal, bool $profile, Participant $participant, DishCollection $combinedDishes
    ): array {
        $participantData = [];
        $participantData['booked'][] = $meal->getDish()->getId();
        $participantData['isOffering'][] = $participant->isPending();
        if (true === $profile) {
            $participantData['profile'] = $participant->getProfile()->getUsername();
        }

        foreach ($combinedDishes as $dish) {
            $participantData['booked'][] = $dish->getId();
        }

        return $participantData;
    }
}
