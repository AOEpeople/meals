<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\AccountingBundle\Repository\PriceRepository;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\MealBundle\Repository\WeekRepositoryInterface;
use App\Mealz\MealBundle\Service\CombinedMealService;
use App\Mealz\MealBundle\Service\Exception\PriceNotFoundException;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use App\Mealz\MealBundle\Tests\Mocks\LoggerMock;
use Doctrine\ORM\EntityManagerInterface;
use Override;

final class CombinedMealServiceTest extends AbstractDatabaseTestCase
{
    private CombinedMealService $cms;
    private Dish $combinedDish;
    private LoggerMock $loggerMock;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            new LoadWeeks(),
            new LoadDays(),
            new LoadCategories(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadMeals(),
        ]);

        /* @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        /* https://stackoverflow.com/questions/73209831/unitenum-cannot-be-cast-to-string */

        $dishRepo = static::getContainer()->get(DishRepository::class);
        $priceRepo = static::getContainer()->get(PriceRepository::class);
        $this->loggerMock = new LoggerMock();
        $this->cms = new CombinedMealService($entityManager, $dishRepo, $priceRepo, $this->loggerMock);

        $combinedDishes = $dishRepo->findBy(['slug' => Dish::COMBINED_DISH_SLUG]);
        if (1 === count($combinedDishes)) {
            $this->combinedDish = $combinedDishes[0];
        }
    }

    public function testUpdateWeekIsValid(): void
    {
        /** @var WeekRepositoryInterface $weekRepository */
        $weekRepository = self::getContainer()->get(WeekRepositoryInterface::class);
        $week = $weekRepository->getCurrentWeek();
        $this->assertNotNull($week);
        $this->assertNotEmpty($week->getDays());

        $hasMeals = false;

        /** @var Day $day */
        foreach ($week->getDays() as $day) {
            /** @var Meal $meal */
            foreach ($day->getMeals() as $meal) {
                $hasMeals = true;
                $this->assertNotEquals($meal->getDish()->getId(), $this->combinedDish->getId());
            }
        }

        $this->assertTrue($hasMeals);

        $this->cms->update($week);

        /** @var Day $day */
        foreach ($week->getDays() as $day) {
            $dayHasOneSizeMeal = false;
            $combinedMeal = null;
            $baseMeals = [];
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

            if (2 <= count($baseMeals) && false === $dayHasOneSizeMeal) {
                $this->assertNotNull($combinedMeal);
                $this->assertEquals($combinedMeal->getDish()->getId(), $this->combinedDish->getId());
            } else {
                $this->assertNull($combinedMeal);
            }
        }

        $this->assertEquals([], $this->loggerMock->logs);
    }

    public function testUpdateWeekWithPriceNotFoundException(): void
    {
        $this->loadFixtures([
            new LoadWeeks(),
            new LoadDays(),
            new LoadCategories(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadMeals(true),
        ]);

        /** @var WeekRepositoryInterface $weekRepository */
        $weekRepository = self::getContainer()->get(WeekRepositoryInterface::class);
        $week = $weekRepository->getCurrentWeek();
        $this->assertNotNull($week);
        $this->assertNotEmpty($week->getDays());

        $hasMeals = false;

        /** @var Day $day */
        foreach ($week->getDays() as $day) {
            /** @var Meal $meal */
            foreach ($day->getMeals() as $meal) {
                $hasMeals = true;
                $this->assertNotEquals($meal->getDish()->getId(), $this->combinedDish->getId());
            }
        }

        $this->assertTrue($hasMeals);

        try {
            $this->cms->update($week);
            $this->fail('PriceNotFoundException was expected to be thrown.');
        } catch (PriceNotFoundException $exception) {
            $this->assertSame('Price not found for year "2025".', $exception->getMessage());
        }

        $this->assertEquals([
            'error' => [
                [
                    'message' => 'Combined dish price by year does not exist.',
                    'context' => [
                        'year' => 2025
                    ]
                ]
            ]
        ], $this->loggerMock->logs);
    }
}
