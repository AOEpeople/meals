<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Repository\DishRepository;
use Doctrine\ORM\EntityManagerInterface;

class CombinedMealService
{
    private const string COMBINED_DISH_TITLE_EN = 'Combined Dish'; // NOTE: important for slug generation, do not change

    private float $defaultPrice;

    private EntityManagerInterface $entityManager;

    private Dish $combinedDish;

    public function __construct(
        float $combinedPrice,
        EntityManagerInterface $entityManager,
        DishRepository $dishRepo
    ) {
        $this->defaultPrice = $combinedPrice;
        $this->entityManager = $entityManager;
        $this->combinedDish = $this->createCombinedDish($dishRepo);
    }

    public function update(Week $week): void
    {
        $update = false;
        /** @var Day $day */
        foreach ($week->getDays() as $day) {
            if ($day->getMeals()->count() < 1) {
                continue;
            }

            $combinedMeal = null;
            $dayHasOneSizeMeal = false;
            $baseMeals = []; // NOTE: in case of variations, we only need the parent
            /** @var Meal $meal */
            foreach ($day->getMeals() as $meal) {
                if (null === $combinedMeal && $this->combinedDish->getSlug() === $meal->getDish()->getSlug()) {
                    $combinedMeal = $meal;
                } elseif (null === $meal->getDish()->getParent()) {
                    $baseMeals[$meal->getId()] = $meal;
                } elseif (null !== $meal->getDish()->getParent()) {
                    $baseMeals[$meal->getDish()->getParent()->getId()] = $meal->getDish()->getParent();
                }

                if (true === $meal->getDish()->hasOneServingSize()) {
                    $dayHasOneSizeMeal = true;
                }
            }

            if (null === $combinedMeal && 1 < count($baseMeals) && false === $dayHasOneSizeMeal) {
                $this->createCombinedMeal($day);
                $update = true;
            } elseif (null !== $combinedMeal && (1 >= count($baseMeals) || true === $dayHasOneSizeMeal)) {
                $this->removeCombinedMeal($day, $combinedMeal);
                $update = true;
            }
        }

        if (true === $update) {
            $this->entityManager->persist($week);
            $this->entityManager->flush();
        }
    }

    private function createCombinedDish(DishRepository $dishRepo): Dish
    {
        $combinedDishes = $dishRepo->findBy(['slug' => Dish::COMBINED_DISH_SLUG]);
        if (1 === count($combinedDishes)) {
            return $combinedDishes[0];
        }

        $combinedDish = new Dish();
        $combinedDish->setEnabled(false);
        $combinedDish->setPrice($this->defaultPrice);
        $combinedDish->setTitleEn(self::COMBINED_DISH_TITLE_EN);
        $combinedDish->setTitleDe('Kombi-Gericht');
        $combinedDish->setDescriptionEn('');
        $combinedDish->setDescriptionDe('');

        $this->entityManager->persist($combinedDish);
        $this->entityManager->flush();

        return $combinedDish;
    }

    private function createCombinedMeal(Day $day): void
    {
        $combinedMeal = new Meal($this->combinedDish, $day);
        $combinedMeal->setPrice($this->defaultPrice);

        $day->addMeal($combinedMeal);
    }

    private function removeCombinedMeal(Day $day, Meal $combinedMeal): void
    {
        $day->removeMeal($combinedMeal);
        $this->entityManager->remove($combinedMeal);
    }
}
