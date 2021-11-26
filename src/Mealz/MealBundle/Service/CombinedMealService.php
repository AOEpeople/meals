<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class CombinedMealService
{
    private float $defaultPrice;

    private LoggerInterface $logger;

    private EntityManagerInterface $entityManager;

    public function __construct(float $combinedPrice, LoggerInterface $logger, EntityManagerInterface $entityManager/*, DishCombinationRepository $dishCombinationRepo*/)
    {
        $this->defaultPrice = $combinedPrice;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    public function update(Week $week)
    {
        /** @var Day $day */
        foreach ($week->getDays() as $day) {
            /** @var Meal $combinedMeal */
            $combinedMeals = $day->getMeals()->filter(function (Meal $m) {
                return null === $m->getDish(); // for now it's null but there should be one "generic" dish representing a dish combination
            });

            if ($combinedMeals->isEmpty()) {
                $this->logger->info("Create new one combined meal");

                $combinedMeal = new Meal();
                $combinedMeal->setDay($day);
                $combinedMeal->setDateTime(clone $day->getDateTime());
                $combinedMeal->setDish(null); // for now it's null but there should be one "generic" dish representing a dish combination
                $combinedMeal->setPrice($this->defaultPrice);

                $this->entityManager->persist($combinedMeal);
                $this->entityManager->flush();
            } else {
                $this->logger->info("Combined meal already exists.");
            }

            $numberOfMeals = count($day->getMeals());
            $this->logger->info("Meals for that day", ["day" => $day->getDateTime(), "count" => $numberOfMeals]);
        }
    }
}