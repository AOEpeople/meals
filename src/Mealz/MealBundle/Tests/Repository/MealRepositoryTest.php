<?php

namespace App\Mealz\MealBundle\Tests\Repository;

use App\Mealz\MealBundle\Entity\Meal;
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

        $this->mealRepository = $this->getDoctrine()->getRepository(Meal::class);
        $this->clearAllTables();
    }

    public function testFindOneByDateAndDish(): void
    {
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $this->persistAndFlushAll([$dish, $meal]);

        $result = $this->mealRepository->findOneByDateAndDish(date('Y-m-d'), $dish->getSlug());

        $this->assertEquals($meal, $result);
    }

    public function testFindOneByDateAndDishInvalidDate(): void
    {
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $this->persistAndFlushAll([$dish, $meal]);

        $this->expectException(InvalidArgumentException::class);

        $this->mealRepository->findOneByDateAndDish(date('Y-m-'), $dish->getSlug());
    }
    
    public function testFindOneByDateAndDishNoResults(): void
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll([$dish]);

        $result = $this->mealRepository->findOneByDateAndDish(date('Y-m-d'), $dish->getSlug());

        $this->assertNull($result);
    }
}
