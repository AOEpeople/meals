<?php

namespace Mealz\MealBundle\Tests\Repository;

use Mealz\MealBundle\Entity\MealRepository;
use Mealz\MealBundle\Tests\AbstractDatabaseTestCase;

class MealRepositoryTest extends AbstractDatabaseTestCase
{
    /** @var  MealRepository */
    protected $mealRepository;

    public function setUp()
    {
        parent::setUp();
        $this->mealRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
        $this->clearAllTables();
    }

    public function testFindOneByDateAndDish()
    {
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $this->persistAndFlushAll([$dish, $meal]);

        $result = $this->mealRepository->findOneByDateAndDish(date('Y-m-d'), $dish->getSlug());

        $this->assertEquals($meal, $result);
    }

    public function testFindOneByDateAndDishInvalidDate()
    {
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $this->persistAndFlushAll([$dish, $meal]);

        $this->setExpectedException('\InvalidArgumentException');

        $this->mealRepository->findOneByDateAndDish(date('Y-m-'), $dish->getSlug());

    }

    public function testFindOneByDateAndDishMultipleResults()
    {
        $this->markTestSkipped(
            'Currently not required. Makes no sense to choose same meal for one day. Maybbe we need the test again'
        );
        
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $meal2 = $this->createMeal($dish);
        $this->persistAndFlushAll([$dish, $meal, $meal2]);

        $this->setExpectedException('\LogicException');

        $this->mealRepository->findOneByDateAndDish(date('Y-m-d'), $dish->getSlug());
    }

    public function testFindOneByDateAndDishNoResults()
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll([$dish]);

        $result = $this->mealRepository->findOneByDateAndDish(date('Y-m-d'), $dish->getSlug());

        $this->assertNull($result);
    }
}