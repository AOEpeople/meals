<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Week;

use App\Mealz\MealBundle\Controller\Exceptions\InvalidMealNewWeekDataException;
use App\Mealz\MealBundle\Day\DayUpdateHandlerInterface;
use App\Mealz\MealBundle\Service\WeekService;
use App\Mealz\MealBundle\Week\Model\WeekId;
use App\Mealz\MealBundle\Week\Model\WeekNotification;
use App\Mealz\MealBundle\Week\WeekCreateHandler;
use App\Mealz\MealBundle\Week\WeekExistingValidatorInterface;
use App\Mealz\MealBundle\Week\WeekPersisterInterface;
use DateTime;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Mealz\MealBundle\Week\WeekCreateHandler
 */
final class WeekCreateHandlerTest extends TestCase
{
    private MockObject $weekExistsValidatorMock;
    private MockObject $dayUpdateHandlerMock;
    private MockObject $weekPersisterMock;
    private string $lockParticipationAt;
    private WeekCreateHandler $weekCreateHandler;

    #[Override]
    protected function setUp(): void
    {
        $this->weekExistsValidatorMock = $this->getMockBuilder(WeekExistingValidatorInterface::class)->disableOriginalConstructor()->getMock();
        $this->dayUpdateHandlerMock = $this->getMockBuilder(DayUpdateHandlerInterface::class)->disableOriginalConstructor()->getMock();
        $this->weekPersisterMock = $this->getMockBuilder(WeekPersisterInterface::class)->disableOriginalConstructor()->getMock();
        $this->lockParticipationAt = '-1 day 16:00';
        $this->weekCreateHandler = new WeekCreateHandler(
            $this->weekExistsValidatorMock,
            $this->dayUpdateHandlerMock,
            $this->weekPersisterMock,
            $this->lockParticipationAt
        );
    }

    public function testHandleAndGetIsValid(): void
    {
        $date = new DateTime('2025-01-02');
        $payload = [
            'enabled' => true,
            'days' => [
                [
                    'id' => 0,
                    'date' => '2025-01-01',
                    'events' => [],
                    'meals' => [],
                    'lockDate' => null,
                ],
            ],
            'notify' => false,
        ];
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $persisterWeekId = new WeekId(1);
        $week = WeekService::generateEmptyWeek($date, $this->lockParticipationAt);
        $week->setEnabled(true);
        $weekNotification = new WeekNotification(false);
        $this->weekPersisterMock->expects(self::once())
            ->method('persist')
            ->with($week, $weekNotification)
            ->willReturn($persisterWeekId);
        $this->weekExistsValidatorMock->expects(self::once())
            ->method('validate')
            ->with($date);
        $this->dayUpdateHandlerMock->expects(self::once())
            ->method('handle')
            ->with([
                'id' => 0,
                'date' => '2025-01-01',
                'events' => [],
                'meals' => [],
                'lockDate' => null
            ], $week->getDays()[0], 2);

        $weekId = $this->weekCreateHandler->handleAndGet($date, $request);

        $this->assertSame($persisterWeekId->value, $weekId->value);
    }

    public function testHandleAndGetWithInvalidMealNewWeekDataException(): void
    {
        $date = new DateTime('2025-01-02');
        $payload = [
            'days' => [
                [
                    'id' => 0,
                    'date' => '2025-01-01',
                    'events' => [],
                    'meals' => [],
                    'lockDate' => null,
                ],
            ],
            'notify' => false,
        ];
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $persisterWeekId = new WeekId(1);
        $week = WeekService::generateEmptyWeek($date, $this->lockParticipationAt);
        $week->setEnabled(true);
        $weekNotification = new WeekNotification(false);
        $this->weekPersisterMock->expects(self::never())
            ->method('persist')
            ->with($week, $weekNotification)
            ->willReturn($persisterWeekId);
        $this->weekExistsValidatorMock->expects(self::once())
            ->method('validate')
            ->with($date);
        $this->dayUpdateHandlerMock->expects(self::never())
            ->method('handle')
            ->with([
                'id' => 0,
                'date' => '2025-01-01',
                'events' => [],
                'meals' => [],
                'lockDate' => null,
            ], $week->getDays()[0], 2);

        try {
            $this->weekCreateHandler->handleAndGet($date, $request);
            $this->fail('InvalidMealNewWeekDataException was expected to be thrown.');
        } catch (InvalidMealNewWeekDataException $exception) {
            $this->assertSame('Request body contains invalid data.', $exception->getMessage());
        }
    }
}
