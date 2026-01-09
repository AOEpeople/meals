<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\AccountingBundle\Entity\Price;
use App\Mealz\AccountingBundle\Repository\PriceRepositoryInterface;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Helper\Exceptions\PriceNotFoundException;
use App\Mealz\MealBundle\Helper\MealAdminHelper;
use App\Mealz\MealBundle\Repository\DishRepositoryInterface;
use App\Mealz\MealBundle\Repository\EventPartRepoInterface;
use App\Mealz\MealBundle\Repository\EventRepositoryInterface;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Tests\Mocks\LoggerMock;
use DateTimeImmutable;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Mealz\MealBundle\Helper\MealAdminHelper
 */
final class MealAdminHelperTest extends TestCase
{
    private MockObject $eventRepositoryMock;
    private MockObject $eventPartRepoMock;
    private MockObject $dishRepositoryMock;
    private MockObject $mealRepositoryMock;
    private MockObject $priceRepositoryMock;
    private LoggerMock $loggerMock;
    private MealAdminHelper $mealAdminHelper;

    #[Override]
    protected function setUp(): void
    {
        $this->eventRepositoryMock = $this->getMockBuilder(EventRepositoryInterface::class)->disableOriginalConstructor()->getMock();
        $this->eventPartRepoMock = $this->getMockBuilder(EventPartRepoInterface::class)->disableOriginalConstructor()->getMock();
        $this->dishRepositoryMock = $this->getMockBuilder(DishRepositoryInterface::class)->disableOriginalConstructor()->getMock();
        $this->mealRepositoryMock = $this->getMockBuilder(MealRepositoryInterface::class)->disableOriginalConstructor()->getMock();
        $this->priceRepositoryMock = $this->getMockBuilder(PriceRepositoryInterface::class)->disableOriginalConstructor()->getMock();
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

    public function testHandleMealArrayIsValid(): void
    {
        $mealArr = [
            [
                'dishSlug' => 'pasta',
                'participationLimit' => 12,
            ]
        ];
        $dayEntity = new Day();

        $dish = new Dish();
        $this->dishRepositoryMock->expects(self::once())
            ->method('findOneBy')
            ->with(['slug' => 'pasta'])
            ->willReturn($dish);
        $price = new Price();
        $dateTime = new DateTimeImmutable('now');
        $dateTimeYearAsInt = (int) $dateTime->format('Y');
        $this->priceRepositoryMock->expects(self::once())
            ->method('findByYear')
            ->with($dateTimeYearAsInt)
            ->willReturn($price);
        $this->mealAdminHelper->handleMealArray($mealArr, $dayEntity);
        $this->assertEquals([], $this->loggerMock->logs);
    }

    public function testHandleMealArrayWithPriceNotFoundException(): void
    {
        $mealArr = [
            [
                'dishSlug' => 'pasta',
                'participationLimit' => 12,
            ]
        ];
        $dayEntity = new Day();

        $dish = new Dish();
        $this->dishRepositoryMock->expects(self::once())
            ->method('findOneBy')
            ->with(['slug' => 'pasta'])
            ->willReturn($dish);
        $dateTime = new DateTimeImmutable('now');
        $dateTimeYearAsInt = (int) $dateTime->format('Y');
        $this->priceRepositoryMock->expects(self::once())
            ->method('findByYear')
            ->with($dateTimeYearAsInt)
            ->willReturn(null);
        try {
            $this->mealAdminHelper->handleMealArray($mealArr, $dayEntity);
            $this->fail('PriceNotFoundException was expected to be thrown.');
        } catch (PriceNotFoundException $exception) {
            $this->assertSame(sprintf('Price not found for year "%d".', $dateTimeYearAsInt), $exception->getMessage());
        }

        $this->assertEquals([
            'error' => [
                [
                    'message' => 'Prices could not be loaded by price repository in handleMealArray.',
                    'context' => [
                        'year' => $dateTimeYearAsInt
                    ]
                ]
            ]
        ], $this->loggerMock->logs);
    }
}
