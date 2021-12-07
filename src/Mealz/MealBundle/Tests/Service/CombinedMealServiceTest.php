<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishRepository;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\Service\CombinedMealService;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use Doctrine\ORM\EntityManagerInterface;

class CombinedMealServiceTest extends AbstractDatabaseTestCase
{
    private CombinedMealService $cms;
    private Dish $combinedDish;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            new LoadWeeks(),
            new LoadDays(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadMeals(),
        ]);

        /* @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $price = self::$kernel->getContainer()->getParameter('mealz.meal.combined.price');
        $dishRepo = static::$container->get(DishRepository::class);
        $this->cms = new CombinedMealService($price, $entityManager, $dishRepo);

        $combinedDishes = $dishRepo->findBy(['slug' => CombinedMealService::COMBINED_DISH_SLUG]);
        if (1 === count($combinedDishes)) {
            $this->combinedDish = $combinedDishes[0];
        }
    }

    /**
     * @test
     */
    public function updateWeek(): void
    {
        /** @var WeekRepository $weekRepository */
        $weekRepository = $this->getDoctrine()->getRepository(Week::class);
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
            }

            if (2 <= count($baseMeals)) {
                $this->assertNotNull($combinedMeal);
                $this->assertEquals($combinedMeal->getDish()->getId(), $this->combinedDish->getId());
            } else {
                $this->assertNull($combinedMeal);
            }
        }
    }
}
