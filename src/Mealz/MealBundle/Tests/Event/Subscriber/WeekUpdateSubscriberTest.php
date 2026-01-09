<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Event\Subscriber;

use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Event\Subscriber\WeekUpdateSubscriber;
use App\Mealz\MealBundle\Event\WeekUpdateEvent;
use App\Mealz\MealBundle\Message\WeeklyMenuMessage;
use App\Mealz\MealBundle\Service\CombinedMealServiceInterface;
use App\Mealz\MealBundle\Service\Exception\PriceNotFoundException;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use App\Mealz\MealBundle\Tests\Mocks\LoggerMock;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \App\Mealz\MealBundle\Event\Subscriber\WeekUpdateSubscriber
 */
final class WeekUpdateSubscriberTest extends TestCase
{
    private MockObject $combinedMealServiceMock;
    private MockObject $notifierMock;
    private MockObject $translatorMock;
    private LoggerMock $loggerMock;
    private WeekUpdateSubscriber $weekUpdateSubscriber;

    #[Override]
    protected function setUp(): void
    {
        $this->combinedMealServiceMock = $this->getMockBuilder(CombinedMealServiceInterface::class)->disableOriginalConstructor()->getMock();
        $this->notifierMock = $this->getMockBuilder(NotifierInterface::class)->disableOriginalConstructor()->getMock();
        $this->translatorMock = $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock();
        $this->loggerMock = new LoggerMock();
        $this->weekUpdateSubscriber = new WeekUpdateSubscriber(
            $this->combinedMealServiceMock,
            $this->notifierMock,
            $this->translatorMock,
            $this->loggerMock,
        );
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function testOnWeekUpdateIsValid(): void
    {
        $week = new Week();
        $weekUpdateEvent = new WeekUpdateEvent($week, true);

        $this->combinedMealServiceMock->expects(self::once())
            ->method('update')
            ->with($week);
        $expectedWeeklyMenuMessage = new WeeklyMenuMessage($week, $this->translatorMock);
        $this->notifierMock->expects(self::once())
            ->method('send')
            ->with($expectedWeeklyMenuMessage);

        $this->weekUpdateSubscriber->onWeekUpdate($weekUpdateEvent);

        $this->assertEquals([], $this->loggerMock->logs);
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function testOnWeekUpdateWithPriceNotFoundException(): void
    {
        $week = new Week();
        $weekUpdateEvent = new WeekUpdateEvent($week, true);

        $this->combinedMealServiceMock->expects(self::once())
            ->method('update')
            ->with($week)
            ->willThrowException(new PriceNotFoundException('test'));
        $expectedWeeklyMenuMessage = new WeeklyMenuMessage($week, $this->translatorMock);
        $this->notifierMock->expects(self::never())
            ->method('send')
            ->with($expectedWeeklyMenuMessage);

        $this->weekUpdateSubscriber->onWeekUpdate($weekUpdateEvent);

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
