<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Event\Subscriber;

use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Event\Subscriber\WeekUpdateSubscriber;
use App\Mealz\MealBundle\Event\WeekUpdateEvent;
use App\Mealz\MealBundle\Message\WeeklyMenuMessage;
use App\Mealz\MealBundle\Service\Exception\PriceNotFoundException;
use App\Mealz\MealBundle\Tests\Event\Subscriber\Mocks\CombinedMealServiceMock;
use App\Mealz\MealBundle\Tests\Event\Subscriber\Mocks\NotifierMock;
use App\Mealz\MealBundle\Tests\Event\Subscriber\Mocks\TranslatorMock;
use App\Mealz\MealBundle\Tests\Mocks\LoggerMock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Mealz\MealBundle\Event\Subscriber\WeekUpdateSubscriber
 */
final class WeekUpdateSubscriberTest extends TestCase
{
    private CombinedMealServiceMock $combinedMealServiceMock;
    private NotifierMock $notifierMock;
    private TranslatorMock $translatorMock;
    private LoggerMock $loggerMock;
    private WeekUpdateSubscriber $weekUpdateSubscriber;

    protected function setUp(): void
    {
        $this->combinedMealServiceMock = new CombinedMealServiceMock();
        $this->notifierMock = new NotifierMock();
        $this->translatorMock = new TranslatorMock();
        $this->loggerMock = new LoggerMock();
        $this->weekUpdateSubscriber = new WeekUpdateSubscriber(
            $this->combinedMealServiceMock,
            $this->notifierMock,
            $this->translatorMock,
            $this->loggerMock,
        );
    }

    public function testOnWeekUpdateIsValid(): void
    {
        $week = new Week();
        $weekUpdateEvent = new WeekUpdateEvent($week, true);

        $this->notifierMock->outputIsNotified = true;
        $this->translatorMock->outputTransValue = 'week.notification.header.no_week';

        $this->weekUpdateSubscriber->onWeekUpdate($weekUpdateEvent);

        $this->assertEquals($week, $this->combinedMealServiceMock->inputWeek);
        $expectedWeeklyMenuMessage = new WeeklyMenuMessage($week, $this->translatorMock);
        $this->assertEquals($expectedWeeklyMenuMessage, $this->notifierMock->inputMessage);
        $this->assertEquals([], $this->loggerMock->logs);
    }

    public function testOnWeekUpdateWithPriceNotFoundException(): void
    {
        $week = new Week();
        $weekUpdateEvent = new WeekUpdateEvent($week, true);

        $this->notifierMock->outputIsNotified = true;
        $this->translatorMock->outputTransValue = 'week.notification.header.no_week';
        $this->combinedMealServiceMock->throwPriceNotFoundException = new PriceNotFoundException('test');

        $this->weekUpdateSubscriber->onWeekUpdate($weekUpdateEvent);

        $this->assertEquals($week, $this->combinedMealServiceMock->inputWeek);
        $this->assertEquals([
            'error' => [
                [
                    'message' => 'Week could not be updated.',
                    'context' => [
                        'exceptionMessage' => 'test'
                    ]
                ]
            ]
        ], $this->loggerMock->logs);
    }
}
