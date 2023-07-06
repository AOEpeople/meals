<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DayService
{
    private MealRepositoryInterface $mealRepository;
    private EntityManagerInterface $em;

    public function __construct(
        MealRepositoryInterface $mealRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->mealRepository = $mealRepository;
        $this->em = $entityManager;
    }

    public function isMealInDay(Day $day, int $mealId): bool
    {
        /** @var Meal $meal */
        $meal = $this->mealRepository->find($mealId);
        if (null !== $meal && $meal->getDay()->getId() === $day->getId()) {
            return true;
        }

        return false;
    }

    public function isDishInDay(Day $day, string $dishSlug): bool
    {
        foreach ($day->getMeals() as $meal) {
            $dish = $meal->getDish();
            if ($dish->getSlug() === $dishSlug) {
                return true;
            }
        }

        return false;
    }

    public function mealHasParticipations(int $mealId): bool
    {
        /** @var Meal $meal */
        $meal = $this->mealRepository->find($mealId);

        return $meal->hasParticipations();
    }

    public function removeUnusedMeals(Day $day, array $mealCollection): void
    {
        foreach ($day->getMeals() as $mealEntity) {
            $this->removeUnusedMealHelper($mealEntity, $mealCollection, $day);
        }
    }

    private function removeUnusedMealHelper(Meal $mealEntity, array $mealCollection, Day $day)
    {
        $canRemove = true;
        foreach ($mealCollection as $mealArr) {
            if (!$canRemove) {
                break;
            }
            foreach ($mealArr as $meal) {
                // if dish is null and mealId is set the meal is removed
                if ($mealEntity->getId() === $meal['mealId'] && isset($meal['dishSlug'])) {
                    $canRemove = false;
                }
                if (!$canRemove) {
                    break;
                }
            }
        }
        if (!$mealEntity->hasParticipations() && $canRemove) {
            $day->removeMeal($mealEntity);
            $this->em->remove($mealEntity);
        }
    }
}
