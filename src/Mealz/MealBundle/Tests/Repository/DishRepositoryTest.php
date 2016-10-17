<?php

namespace Mealz\MealBundle\Tests\Repository;

use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\DishRepository;
use Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use Mealz\MealBundle\EventListener\LocalisationListener;

// @TODO: check if load_category=false option is working
class DishRepositoryTest extends AbstractDatabaseTestCase
{
    /** @var  DishRepository */
    protected $dishRepository;

    protected $locale;

    public function setUp()
    {
        parent::setUp();
        $this->dishRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Dish');
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

    public function testHasDishAssociatedMealsWithOneAssociation(){
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $this->persistAndFlushAll([$dish, $meal]);
        $result = $this->dishRepository->hasDishAssociatedMeals($dish);
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
        usort($dishes, function($a, $b) {
            /** @var Dish $a */
            /** @var Dish $b */
            return $a->getTitle() < $b->getTitle() ? 1 : -1;
        });
    }

    /**
     * @param Dish[] $dishes
     */
    protected function sortDishByCategoryAndTitle(&$dishes)
    {
        usort(
            $dishes,
            function ($a, $b) {
                /** @var Dish $a */
                /** @var Dish $b */
                if ($a->getCategory()->getTitle() === $b->getCategory()->getTitle()) {
                    return $a->getTitle() < $b->getTitle() ? -1 : 1;
                }

                return $a->getCategory()->getTitle() < $b->getCategory()->getTitle() ? -1 : 1;
            }
        );
    }

    protected function createMultipleDishes($count)
    {
        $dishes = array();
        $categories = $this->createMultipleCategories($count / 2);
        for($i = 0; $i < $count; $i++) {
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
        $qb = $this->dishRepository->getSortedDishesQueryBuilder($options);
        $result = $qb->getQuery()->execute();

        foreach($result as $dish) {
            // Simulate doctrine postLoad localization listener
            $dish->setCurrentLocale($this->locale);
            $dish->getCategory()->setCurrentLocale($this->locale);
        }

        $diff = array_diff_assoc($dishes, $result);

        $this->assertEmpty($diff);
    }
}