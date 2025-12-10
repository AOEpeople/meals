<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\AccountingBundle\Entity\Price;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Helper\Exceptions\PriceNotFoundException;
use App\Mealz\MealBundle\Helper\MealAdminHelper;
use App\Mealz\MealBundle\Tests\Mocks\LoggerMock;
use App\Mealz\MealBundle\Tests\Service\Mocks\DishRepositoryMock;
use App\Mealz\MealBundle\Tests\Service\Mocks\EventPartRepoMock;
use App\Mealz\MealBundle\Tests\Service\Mocks\EventRepositoryMock;
use App\Mealz\MealBundle\Tests\Service\Mocks\MealRepositoryMock;
use App\Mealz\MealBundle\Tests\Service\Mocks\PriceRepositoryMock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Mealz\MealBundle\Helper\MealAdminHelper
 */
final class MealAdminHelperTest extends TestCase
{
    private EventRepositoryMock $eventRepositoryMock;
    private EventPartRepoMock $eventPartRepoMock;
    private DishRepositoryMock $dishRepositoryMock;
    private MealRepositoryMock $mealRepositoryMock;
    private PriceRepositoryMock $priceRepositoryMock;
    private LoggerMock $loggerMock;
    private MealAdminHelper $mealAdminHelper;

    protected function setUp(): void
    {
        $this->eventRepositoryMock = new EventRepositoryMock();
        $this->eventPartRepoMock = new EventPartRepoMock();
        $this->dishRepositoryMock = new DishRepositoryMock();
        $this->mealRepositoryMock = new MealRepositoryMock();
        $this->priceRepositoryMock = new PriceRepositoryMock();
        $this->loggerMock = new LoggerMock();
        $this->mealAdminHelper = new MealAdminHelper(
            $this->eventRepositoryMock,
            $this->eventPartRepoMock,
            $this->dishRepositoryMock,
            $this->mealRepositoryMock,
            $this->priceRepositoryMock,
            $this->loggerMock
        );
    }

    public function testHandleMealArray_IsValid(): void
    {
        $mealArr = [
            [
                'dishSlug' => 'pasta',
                'participationLimit' => 12,
            ]
        ];
        $dayEntity = new Day();

        $dish = new Dish();
        $this->dishRepositoryMock->outputFindOneBy = $dish;
        $price = new Price();
        $this->priceRepositoryMock->outputFindByYear = $price;
        $this->mealAdminHelper->handleMealArray($mealArr, $dayEntity);

        $this->assertEquals([
            [
                'slug' => 'pasta'
            ]
        ], $this->dishRepositoryMock->findOneByCriteria);
        $currentDateTime = new \DateTimeImmutable('now');
        $currentYearAsInt = (int)$currentDateTime->format('Y');
        $this->assertEquals([
            $currentYearAsInt
        ], $this->priceRepositoryMock->inputFindByYearInputs);
        $this->assertEquals([], $this->loggerMock->logs);
    }

    public function testHandleMealArray_WithPriceNotFoundException(): void
    {
        $mealArr = [
            [
                'dishSlug' => 'pasta',
                'participationLimit' => 12,
            ]
        ];
        $dayEntity = new Day();

        $dish = new Dish();
        $this->dishRepositoryMock->outputFindOneBy = $dish;
        $this->priceRepositoryMock->outputFindByYear = null;
        try {
            $this->mealAdminHelper->handleMealArray($mealArr, $dayEntity);
            $this->fail('PriceNotFoundException was expected to be thrown.');
        } catch (PriceNotFoundException $exception) {
            $this->assertSame('Price not found for year "2025".', $exception->getMessage());
        }

        $this->assertEquals([
            [
                'slug' => 'pasta'
            ]
        ], $this->dishRepositoryMock->findOneByCriteria);
        $currentDateTime = new \DateTimeImmutable('now');
        $currentYearAsInt = (int)$currentDateTime->format('Y');
        $this->assertEquals([
            $currentYearAsInt
        ], $this->priceRepositoryMock->inputFindByYearInputs);
        $this->assertEquals([
            'error' => [
                [
                    'message' => 'Prices could not be loaded by price repository in handleMealArray.',
                    'context' => [
                        'year' => 2025
                    ]
                ]
            ]
        ], $this->loggerMock->logs);
    }
}