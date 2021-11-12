<?php

namespace App\Mealz\MealBundle\Tests\Repository;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishRepository;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use App\Mealz\MealBundle\EventListener\LocalisationListener;

// @TODO: check if load_category=false option is working
class DishRepositoryTest extends AbstractDatabaseTestCase
{
    /** @var  DishRepository */
    protected $dishRepository;

    protected $locale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dishRepository = $this->getDoctrine()->getRepository(Dish::class);
        $this->locale = 'en';
        $this->clearAllTables();
    }

    public function testGetSortedDishesQueryBuilderOrderByTitle()
    {
        $dishes = $this->createMultipleDishes(10);
        $this->sortDishByTitle($dishes);

        $options = array(
            'load_category' => true,
            'orderBy_category' => false
        );

        $this->setRepositoryLocalization();
        $this->assertNoQueryResultDiff($dishes, $options);
    }

    public function testGetSortedDishesQueryBuilderOrderByCategory()
    {
        $dishes = $this->createMultipleDishes(10);
        $this->sortDishByCategoryAndTitle($dishes);

        $options = array(
            'load_category' => true,
            'orderBy_category' => true
        );

        $this->setRepositoryLocalization();
        $this->assertNoQueryResultDiff($dishes, $options);
    }

    public function testGetSortedDishesQueryBuilderLocalizedOrderByTitle()
    {
        $this->locale = 'de';
        $dishes = $this->createMultipleDishes(10);
        $this->sortDishByTitle($dishes);

        $options = array(
            'load_category' => true,
            'orderBy_category' => false
        );

        $this->setRepositoryLocalization();
        $this->assertNoQueryResultDiff($dishes, $options);
    }

    public function testGetSortedDishesQueryBuilderLocalizedOrderByCategory()
    {
        $this->locale = 'de';
        $dishes = $this->createMultipleDishes(10);
        $this->sortDishByCategoryAndTitle($dishes);

        $options = array(
            'load_category' => true,
            'orderBy_category' => true
        );

        $this->setRepositoryLocalization();
        $this->assertNoQueryResultDiff($dishes, $options);
    }

    public function testHasDishAssociatedMealsWithNoAssociations()
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll([$dish]);
        $result = $this->dishRepository->hasDishAssociatedMeals($dish);
        $this->assertEmpty($result);
    }

    public function testHasDishAssociatedMealsWithOneAssociation()
    {
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $this->persistAndFlushAll([$dish, $meal]);
        $result = $this->dishRepository->hasDishAssociatedMeals($dish);
        $this->assertTrue($result == 1);
    }

    public function testCountNumberDishWasTakenWithNoCounts()
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll([$dish]);
        $result = $this->dishRepository->countNumberDishWasTaken($dish, '4 weeks ago');
        $this->assertEmpty($result);
    }

    public function testCountNumberDishWasTakenWithAtLeastOneCount()
    {
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $this->persistAndFlushAll([$dish, $meal]);
        $result = $this->dishRepository->countNumberDishWasTaken($dish, '4 weeks ago');
        $this->assertTrue($result == 1);
    }

    public function testCountNumberDishWasTakenWithAtLeastTwoCount()
    {
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $meal2 = $this->createMeal($dish, new \DateTime('2 weeks ago'));
        $this->persistAndFlushAll([$dish, $meal]);
        $this->persistAndFlushAll([$dish, $meal2]);
        $result = $this->dishRepository->countNumberDishWasTaken($dish, '4 weeks ago');
        $this->assertTrue($result == 2);
    }

    public function testCountNumberDishWasTakenWithAtLeastOneValidCount()
    {
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $meal2 = $this->createMeal($dish, new \DateTime('30 weeks ago'));
        $this->persistAndFlushAll([$dish, $meal]);
        $this->persistAndFlushAll([$dish, $meal2]);
        $result = $this->dishRepository->countNumberDishWasTaken($dish, '4 weeks ago');
        $this->assertTrue($result == 1);
    }

    protected function setRepositoryLocalization()
    {
        $localizationListener = $this->getMockBuilder(LocalisationListener::class)
            ->setMethods(array(
                'getLocale'
            ))
            ->disableOriginalConstructor()
            ->getMock();
        $localizationListener->expects($this->atLeastOnce())
            ->method('getLocale')
            ->will($this->returnValue($this->locale));

        $this->dishRepository->setLocalizationListener($localizationListener);
    }

    protected function sortDishByTitle(&$dishes)
    {
        usort($dishes, function ($firstDish, $secondDish) {
            /** @var Dish $firstDish */
            /** @var Dish $secondDish */
            return $firstDish->getTitle() < $secondDish->getTitle() ? 1 : -1;
        });
    }

    /**
     * @param Dish[] $dishes
     */
    protected function sortDishByCategoryAndTitle(&$dishes)
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

    protected function createMultipleDishes($count)
    {
        $dishes = array();
        $categories = $this->createMultipleCategories($count / 2);
        for ($i = 0; $i < $count; $i++) {
            $dish = $this->createDish($categories[array_rand($categories)]);
            $dish->setCurrentLocale($this->locale);
            array_push($dishes, $dish);
        }
        $this->persistAndFlushAll(array_merge($dishes, $categories));
        return $dishes;
    }

    protected function createMultipleCategories($count)
    {
        $categories = array();
        for ($i = 0; $i < $count; $i++) {
            $category = $this->createCategory();
            $category->setCurrentLocale($this->locale);
            array_push($categories, $category);
        }
        return $categories;
    }

    protected function assertNoQueryResultDiff($dishes, $options)
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
}
