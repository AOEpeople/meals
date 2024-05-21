<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Repository;

use App\Mealz\MealBundle\Repository\MealRepository;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use DateTime;

class MealRepositoryTest extends AbstractDatabaseTestCase
{
    /** @var MealRepository */
    protected $mealRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mealRepository = self::getContainer()->get(MealRepositoryInterface::class);
        $this->clearAllTables();
    }

    public function testFindOneByDateAndDish(): void
    {
        $dish = $this->createDish();
        $meal = $this->createMeal($dish);
        $this->persistAndFlushAll([$meal]);

        $result = $this->mealRepository->findOneByDateAndDish(new DateTime(), $dish->getSlug());

        $this->assertEquals($meal, $result);
    }

    public function testFindOneByDateAndDishNoResults(): void
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll([$dish]);

        $result = $this->mealRepository->findOneByDateAndDish(new DateTime(), $dish->getSlug());

        $this->assertNull($result);
    }
}
