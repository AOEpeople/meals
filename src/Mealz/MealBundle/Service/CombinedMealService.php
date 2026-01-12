<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\AccountingBundle\Entity\Price;
use App\Mealz\AccountingBundle\Repository\PriceRepository;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\MealBundle\Service\Exception\PriceNotFoundException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Psr\Log\LoggerInterface;

final class CombinedMealService implements CombinedMealServiceInterface
{
    private const string COMBINED_DISH_TITLE_EN = 'Combined Dish'; // NOTE: important for slug generation, do not change

    private EntityManagerInterface $entityManager;

    private Dish $combinedDish;
    private PriceRepository $priceRepository;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        DishRepository $dishRepo,
        PriceRepository $priceRepo,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->combinedDish = $this->createCombinedDish($dishRepo);
        $this->priceRepository = $priceRepo;
        $this->logger = $logger;
    }

    /**
     * @throws PriceNotFoundException
     */
    #[Override]
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

            $dateTime = new DateTimeImmutable('now');
            $dateTimeYearAsInt = (int) $dateTime->format('Y');
            $price = $this->priceRepository->findByYear($dateTimeYearAsInt);
            if (!($price instanceof Price)) {
                $this->logger->error('Combined dish price by year does not exist.', [
                    'year' => $dateTimeYearAsInt,
                ]);

                throw PriceNotFoundException::isNotFound($dateTimeYearAsInt);
            }
            if (null === $combinedMeal && 1 < count($baseMeals) && false === $dayHasOneSizeMeal) {
                $this->createCombinedMeal($day, $price);
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
        $combinedDish->setTitleEn(self::COMBINED_DISH_TITLE_EN);
        $combinedDish->setTitleDe('Kombi-Gericht');
        $combinedDish->setDescriptionEn('');
        $combinedDish->setDescriptionDe('');

        $this->entityManager->persist($combinedDish);
        $this->entityManager->flush();

        return $combinedDish;
    }

    private function createCombinedMeal(Day $day, Price $price): void
    {
        $combinedMeal = new Meal($this->combinedDish, $price, $day);

        $day->addMeal($combinedMeal);
    }

    private function removeCombinedMeal(Day $day, Meal $combinedMeal): void
    {
        $day->removeMeal($combinedMeal);
        $this->entityManager->remove($combinedMeal);
    }
}
