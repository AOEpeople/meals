<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service\Mocks;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Override;

final class ParticipantRepositoryMock implements ParticipantRepositoryInterface
{
    public array $findInputs = [];
    public mixed $outputFind;
    public array $findAllCalls = [];
    public array $outputFindAll = [];
    public array $findByInputs = [];
    public array $outputFindBy = [];
    public array $findOneByInputs = [];
    public mixed $outputFindOneBy;
    public string $outputGetClassName;
    public array $getParticipantsOnDaysInputs = [];
    public array $outputGetParticipantsOnDays = [];
    public array $getTotalCostInputs = [];
    public float $outputGetTotalCost;
    public array $getLastAccountableParticipationsInputs = [];
    public array $outputGetLastAccountableParticipations = [];
    public array $outputFindCostsGroupedByUserGroupedByMonth = [];
    public array $groupParticipantsByNameInputs = [];
    public array $outputGroupParticipantsByName = [];
    public array $findAllGroupedBySlotAndProfileIDInputs = [];
    public array $outputFindAllGroupedBySlotAndProfileID = [];
    public array $getParticipantsByDayInputs = [];
    public array $outputGetParticipantsByDay = [];
    public array $getParticipantsOnCurrentDayInputs = [];
    public array $outputGetParticipantsOnCurrentDay = [];
    public array $getOfferCountInputs = [];
    public int $outputGetOfferCount;
    public array $getOfferCountByMealInputs = [];
    public int $outputGetOfferCountByMeal;
    public array $isOfferingMealInputs = [];
    public bool $outputIsOfferingMeal;
    public array $getCountBySlotsInputs = [];
    public array $outputGetCountBySlots = [];
    public array $getCountBySlotInputs = [];
    public int $outputGetCountBySlot;
    public array $getCountByMealInputs = [];
    public int $outputGetCountByMeal;
    public array $updateSlotInputs = [];
    public array $removeFutureMealsByProfileInputs = [];
    public array $getParticipationsOfSlotInputs = [];
    public array $outputGetParticipationsOfSlot = [];
    public array $getParticipationCountByProfileInputs = [];
    public int $outputGetParticipationCountByProfile = 0;

    #[Override]
    public function find($id)
    {
        $this->findInputs[] = $id;

        return $this->outputFind;
    }

    #[Override]
    public function findAll()
    {
        $this->findAllCalls[] = true;

        return $this->outputFindAll;
    }

    #[Override]
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
    {
        $this->findByInputs[] = [$criteria, $orderBy, $limit, $offset];

        return $this->outputFindBy;
    }

    #[Override]
    public function findOneBy(array $criteria)
    {
        $this->findOneByInputs[] = $criteria;

        return $this->outputFindOneBy;
    }

    #[Override]
    public function getClassName()
    {
        return $this->outputGetClassName;
    }

    #[Override]
    public function getParticipantsOnDays(DateTime $startDate, DateTime $endDate, ?Profile $profile = null): array
    {
        $this->getParticipantsOnDaysInputs[] = [$startDate, $endDate, $profile];

        return $this->outputGetParticipantsOnDays;
    }

    #[Override]
    public function getTotalCost(string $username): float
    {
        $this->getTotalCostInputs[] = $username;

        return $this->outputGetTotalCost;
    }

    #[Override]
    public function getLastAccountableParticipations(Profile $profile, ?int $limit = null): array
    {
        $this->getLastAccountableParticipationsInputs[] = [$profile, $limit];

        return $this->outputGetLastAccountableParticipations;
    }

    #[Override]
    public function findCostsGroupedByUserGroupedByMonth(): array
    {
        return $this->outputFindCostsGroupedByUserGroupedByMonth;
    }

    #[Override]
    public function groupParticipantsByName(array $participants): array
    {
        $this->groupParticipantsByNameInputs[] = $participants;

        return $this->outputGroupParticipantsByName;
    }

    #[Override]
    public function findAllGroupedBySlotAndProfileID(DateTime $date, bool $getProfile = false): array
    {
        $this->findAllGroupedBySlotAndProfileIDInputs[] = [$date, $getProfile];

        return $this->outputFindAllGroupedBySlotAndProfileID;
    }

    #[Override]
    public function getParticipantsByDay(DateTime $date, array $options = []): array
    {
        $this->getParticipantsByDayInputs[] = [$date, $options];

        return $this->outputGetParticipantsByDay;
    }

    #[Override]
    public function getParticipantsOnCurrentDay(array $options = []): array
    {
        $this->getParticipantsOnCurrentDayInputs[] = $options;

        return $this->outputGetParticipantsOnCurrentDay;
    }

    #[Override]
    public function getOfferCount(DateTime $date): int
    {
        $this->getOfferCountInputs[] = $date;

        return $this->outputGetOfferCount;
    }

    #[Override]
    public function getOfferCountByMeal(Meal $meal): int
    {
        $this->getOfferCountByMealInputs[] = $meal;

        return $this->outputGetOfferCountByMeal;
    }

    #[Override]
    public function isOfferingMeal(Profile $profile, Meal $meal): bool
    {
        $this->isOfferingMealInputs[] = [$profile, $meal];

        return $this->outputIsOfferingMeal;
    }

    #[Override]
    public function getCountBySlots(DateTime $startDate, DateTime $endDate): array
    {
        $this->getCountBySlotsInputs[] = [$startDate, $endDate];

        return $this->outputGetCountBySlots;
    }

    #[Override]
    public function getCountBySlot(Slot $slot, DateTime $date): int
    {
        $this->getCountBySlotInputs[] = [$slot, $date];

        return $this->outputGetCountBySlot;
    }

    #[Override]
    public function getCountByMeal(Meal $meal): int
    {
        $this->getCountByMealInputs[] = $meal;

        return $this->outputGetCountByMeal;
    }

    #[Override]
    public function updateSlot(Profile $profile, DateTime $date, Slot $slot): void
    {
        $this->updateSlotInputs[] = [$profile, $date, $slot];
    }

    #[Override]
    public function removeFutureMealsByProfile(Profile $profile): void
    {
        $this->removeFutureMealsByProfileInputs[] = $profile;
    }

    #[Override]
    public function getParticipationsOfSlot(Slot $slot): array
    {
        $this->getParticipationsOfSlotInputs[] = $slot;

        return $this->outputGetParticipationsOfSlot;
    }

    #[Override]
    public function getParticipationCountByProfile(Profile $profile, DateTime $date): int
    {
        $this->getParticipationCountByProfileInputs[] = [$profile, $date];

        return $this->outputGetParticipationCountByProfile;
    }
}
