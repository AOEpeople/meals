<?php

namespace App\Mealz\MealBundle\Tests\Repository;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\EventListener\LocalisationListener;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;

// @TODO: check if load_category=false option is working
class DishRepositoryTest extends AbstractDatabaseTestCase
{
    use ProphecyTrait;

    /** @var DishRepository */
    protected $dishRepository;

    protected $locale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dishRepository = self::getContainer()->get(DishRepository::class);
        $this->locale = 'en';
        $this->clearAllTables();
    }

    public function testGetSortedDishesQueryBuilderOrderByTitle(): void
    {
        $localisationListener = $this->getMockedLocalisationListener('en');
        $this->dishRepository = $this->getDishRepository($localisationListener);

        $dishes = $this->createMultipleDishes(10);
        $this->sortDishByTitle($dishes);

        $options = [
            'load_category' => true,
            'orderBy_category' => false,
        ];

        $this->assertNoQueryResultDiff($dishes, $options);
    }

    public function testGetSortedDishesQueryBuilderOrderByCategory(): void
    {
        $this->locale = 'en';
        $localisationListener = $this->getMockedLocalisationListener($this->locale);
        $this->dishRepository = $this->getDishRepository($localisationListener);

        $dishes = $this->createMultipleDishes(10);
        $this->sortDishByCategoryAndTitle($dishes);

        $options = [
            'load_category' => true,
            'orderBy_category' => true,
        ];

        $this->assertNoQueryResultDiff($dishes, $options);
    }

    public function testGetSortedDishesQueryBuilderLocalizedOrderByTitle(): void
    {
        $this->locale = 'de';
        $localisationListener = $this->getMockedLocalisationListener($this->locale);
        $this->dishRepository = $this->getDishRepository($localisationListener);
        $dishes = $this->createMultipleDishes(10);
        $this->sortDishByTitle($dishes);

        $options = [
            'load_category' => true,
            'orderBy_category' => false,
        ];

        $this->assertNoQueryResultDiff($dishes, $options);
    }

    public function testGetSortedDishesQueryBuilderLocalizedOrderByCategory(): void
    {
        $this->locale = 'de';
        $localisationListener = $this->getMockedLocalisationListener($this->locale);
        $this->dishRepository = $this->getDishRepository($localisationListener);

        $dishes = $this->createMultipleDishes(10);
        $this->sortDishByCategoryAndTitle($dishes);

        $options = [
            'load_category' => true,
            'orderBy_category' => true,
        ];

        $this->assertNoQueryResultDiff($dishes, $options);
    }

    public function testHasDishAssociatedMealsWithNoAssociations(): void
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll([$dish]);
        $result = $this->dishRepository->hasDishAssociatedMeals($dish);
        $this->assertEmpty($result);
    }

    public function testHasDishAssociatedMealsWithOneAssociation(): void
    {
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $this->persistAndFlushAll([$meal]);
        $result = $this->dishRepository->hasDishAssociatedMeals($dish);
        $this->assertTrue($result);
    }

    public function testCountNumberDishWasTakenWithNoCounts(): void
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll([$dish]);
        $result = $this->dishRepository->countNumberDishWasTaken($dish, '4 weeks ago');
        $this->assertEmpty($result);
    }

    public function testCountNumberDishWasTakenWithAtLeastOneCount(): void
    {
        $dish = $this->createDish();
        $meal = $this->createMeal($dish, new Day());
        $this->persistAndFlushAll([$meal]);
        $result = $this->dishRepository->countNumberDishWasTaken($dish, '4 weeks ago');
        $this->assertTrue(1 == $result);
    }

    public function testCountNumberDishWasTakenWithAtLeastTwoCount(): void
    {
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $day = new Day();
        $day->setDateTime(new DateTime('2 weeks ago'));
        $meal2 = $this->createMeal($dish, $day);
        $this->persistAndFlushAll([$meal, $meal2]);
        $result = $this->dishRepository->countNumberDishWasTaken($dish, '4 weeks ago');
        $this->assertTrue(2 == $result);
    }

    public function testCountNumberDishWasTakenWithAtLeastOneValidCount(): void
    {
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $day = new Day();
        $day->setDateTime(new DateTime('30 weeks ago'));
        $meal2 = $this->createMeal($dish, $day);
        $this->persistAndFlushAll([$meal, $meal2]);
        $result = $this->dishRepository->countNumberDishWasTaken($dish, '4 weeks ago');
        $this->assertTrue(1 == $result);
    }

    protected function sortDishByTitle(&$dishes): void
    {
        usort($dishes, function ($firstDish, $secondDish) {
            /* @var Dish $firstDish */
            /* @var Dish $secondDish */
            return $firstDish->getTitle() < $secondDish->getTitle() ? 1 : -1;
        });
    }

    /**
     * @param Dish[] $dishes
     */
    protected function sortDishByCategoryAndTitle(&$dishes): void
    {
        usort(
            $dishes,
            function ($firstDish, $secondDish) {
                /** @var Dish $firstDish */
                /** @var Dish $secondDish */
                if ($firstDish->getCategory()->getTitle() === $secondDish->getCategory()->getTitle()) {
                    return $firstDish->getTitle() < $secondDish->getTitle() ? -1 : 1;
                }

                return $firstDish->getCategory()->getTitle() < $secondDish->getCategory()->getTitle() ? -1 : 1;
            }
        );
    }

    /**
     * @return Dish[]
     *
     * @psalm-return list<Dish>
     */
    protected function createMultipleDishes(int $count): array
    {
        $dishes = [];
        $categories = $this->createMultipleCategories($count / 2);
        for ($i = 0; $i < $count; ++$i) {
            $dish = $this->createDish($categories[array_rand($categories)]);
            $dish->setCurrentLocale($this->locale);
            array_push($dishes, $dish);
        }
        $this->persistAndFlushAll(array_merge($dishes, $categories));

        return $dishes;
    }

    /**
     * @return \App\Mealz\MealBundle\Entity\Category[]
     *
     * @psalm-return list<\App\Mealz\MealBundle\Entity\Category>
     */
    protected function createMultipleCategories($count): array
    {
        $categories = [];
        for ($i = 0; $i < $count; ++$i) {
            $category = $this->createCategory();
            $category->setCurrentLocale($this->locale);
            array_push($categories, $category);
        }

        return $categories;
    }

    protected function assertNoQueryResultDiff($dishes, $options): void
    {
        $queryBuilder = $this->dishRepository->getSortedDishesQueryBuilder($options);
        $result = $queryBuilder->getQuery()->execute();

        foreach ($result as $dish) {
            // Simulate doctrine postLoad localization listener
            $dish->setCurrentLocale($this->locale);
            $dish->getCategory()->setCurrentLocale($this->locale);
        }

        $diff = array_diff_assoc($dishes, $result);

        $this->assertEmpty($diff);
    }

    private function getDishRepository(LocalisationListener $listener): DishRepository
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);

        return new DishRepository($em, Dish::class, $listener);
    }

    private function getMockedLocalisationListener(string $locale): LocalisationListener
    {
        $prophet = $this->prophesize(LocalisationListener::class);
        /** @psalm-suppress TooManyArguments */
        $prophet->getLocale()->willReturn($locale);

        return $prophet->reveal();
    }
}
