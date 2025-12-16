<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Week;

use App\Mealz\MealBundle\Controller\Exceptions\InvalidMealNewWeekDataException;
use App\Mealz\MealBundle\Tests\Week\Mocks\DayUpdateHandlerMock;
use App\Mealz\MealBundle\Tests\Week\Mocks\WeekExistingValidatorMock;
use App\Mealz\MealBundle\Tests\Week\Mocks\WeekPersisterMock;
use App\Mealz\MealBundle\Week\Model\WeekId;
use App\Mealz\MealBundle\Week\Model\WeekNotification;
use App\Mealz\MealBundle\Week\WeekCreateHandler;
use DateTime;
use Override;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Mealz\MealBundle\Week\WeekCreateHandler
 */
final class WeekCreateHandlerTest extends TestCase
{
    private WeekExistingValidatorMock $weekExistsValidatorMock;
    private DayUpdateHandlerMock $dayUpdateHandlerMock;
    private WeekPersisterMock $weekPersisterMock;
    private string $lockParticipationAt;
    private WeekCreateHandler $weekCreateHandler;

    #[Override]
    protected function setUp(): void
    {
        $this->weekExistsValidatorMock = new WeekExistingValidatorMock();
        $this->dayUpdateHandlerMock = new DayUpdateHandlerMock();
        $this->weekPersisterMock = new WeekPersisterMock();
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
        $this->weekPersisterMock->outputWeekId = $persisterWeekId;

        $weekId = $this->weekCreateHandler->handleAndGet($date, $request);

        $this->assertSame($persisterWeekId->value, $weekId->value);
        $this->assertSame($date->format('Y-m-d'), $this->weekExistsValidatorMock->inputDate->format('Y-m-d'));
        $this->assertEquals([
            [
                'id' => 0,
                'date' => '2025-01-01',
                'events' => [],
                'meals' => [],
                'lockDate' => null,
            ]
        ], $this->dayUpdateHandlerMock->inputDayData);
        $this->assertEquals([
            'id' => null,
            'year' => 2025,
            'calendarWeek' => 1,
            'days' => [
                -1 => [
                    'lockParticipationDateTime' => new DateTime('2024-12-30')->modify($this->lockParticipationAt),
                    'week' => null,
                    'meals' => [],
                    'events' => [],
                    'enabled' => true,
                    'dayId' => null,
                    'dateTime' => new DateTime('2024-12-30T12:00:00.000000+0100'),
                ],
                -2 => [
                    'lockParticipationDateTime' => new DateTime('2024-12-31')->modify($this->lockParticipationAt),
                    'week' => null,
                    'meals' => [],
                    'events' => [],
                    'enabled' => true,
                    'dayId' => null,
                    'dateTime' => new DateTime('2024-12-31T12:00:00.000000+0100'),
                ],
                -3 => [
                    'lockParticipationDateTime' => new DateTime('2025-01-01')->modify($this->lockParticipationAt),
                    'week' => null,
                    'meals' => [],
                    'events' => [],
                    'enabled' => true,
                    'dayId' => null,
                    'dateTime' => new DateTime('2025-01-01T12:00:00.000000+0100'),
                ],
                -4 => [
                    'lockParticipationDateTime' => new DateTime('2025-01-02')->modify($this->lockParticipationAt),
                    'week' => null,
                    'meals' => [],
                    'events' => [],
                    'enabled' => true,
                    'dayId' => null,
                    'dateTime' => new DateTime('2025-01-02T12:00:00.000000+0100'),
                ],
                -5 => [
                    'lockParticipationDateTime' => new DateTime('2025-01-03')->modify($this->lockParticipationAt),
                    'week' => null,
                    'meals' => [],
                    'events' => [],
                    'enabled' => true,
                    'dayId' => null,
                    'dateTime' => new DateTime('2025-01-03T12:00:00.000000+0100'),
                ],
            ],
            'enabled' => true,
        ], $this->dayUpdateHandlerMock->inputDay[0]->getWeek()->jsonSerialize());
        $this->assertEquals([2], $this->dayUpdateHandlerMock->inputCount);
        $this->assertTrue($this->weekPersisterMock->inputWeek->isEnabled());
        $expectedWeekNotification = new WeekNotification(false);
        $this->assertSame($expectedWeekNotification->shouldNotify, $this->weekPersisterMock->inputWeekNotification->shouldNotify);
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
        $this->weekPersisterMock->outputWeekId = $persisterWeekId;

        try {
            $this->weekCreateHandler->handleAndGet($date, $request);
            $this->fail('InvalidMealNewWeekDataException was expected to be thrown.');
        } catch (InvalidMealNewWeekDataException $exception) {
            $this->assertSame('Request body contains invalid data.', $exception->getMessage());
        }

        $this->assertSame($date->format('Y-m-d'), $this->weekExistsValidatorMock->inputDate->format('Y-m-d'));
    }
}
