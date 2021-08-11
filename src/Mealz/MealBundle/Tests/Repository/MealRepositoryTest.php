<?php

namespace App\Mealz\MealBundle\Tests\Repository;

use InvalidArgumentException;
use App\Mealz\MealBundle\Entity\MealRepository;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;

class MealRepositoryTest extends AbstractDatabaseTestCase
{
    /** @var  MealRepository */
    protected $mealRepository;

    protected function setUp(): void
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

        $this->expectException(InvalidArgumentException::class);

        $this->mealRepository->findOneByDateAndDish(date('Y-m-'), $dish->getSlug());
    }
    
    public function testFindOneByDateAndDishNoResults()
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll([$dish]);

        $result = $this->mealRepository->findOneByDateAndDish(date('Y-m-d'), $dish->getSlug());

        $this->assertNull($result);
    }
}
