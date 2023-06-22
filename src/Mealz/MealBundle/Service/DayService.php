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
            if (null !== $dish && $dish->getSlug() === $dishSlug) {
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
            foreach ($mealCollection as $meal) {
                if ($mealEntity->getId() === $meal['mealId']) {
                    continue;
                }
            }
            if (!$mealEntity->hasParticipations()) {
                $day->removeMeal($mealEntity);
                $this->em->remove($mealEntity);
            }
        }
    }
}
