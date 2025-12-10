<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\WeekUpdateEvent;
use App\Mealz\MealBundle\Message\WeeklyMenuMessage;
use App\Mealz\MealBundle\Service\CombinedMealServiceInterface;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

final class WeekUpdateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CombinedMealServiceInterface $combinedMealService,
        // Weekly menu notifier
        private readonly NotifierInterface $notifier,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return string[]
     *
     * @codeCoverageIgnore
     *
     * @psalm-return array{WeekUpdateEvent::class: 'onWeekUpdate'}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            WeekUpdateEvent::class => 'onWeekUpdate',
        ];
    }

    public function onWeekUpdate(WeekUpdateEvent $event): void
    {
        try {
            $week = $event->getWeek();
            $this->combinedMealService->update($week);
            if ($event->getNotify()) {
                $msg = new WeeklyMenuMessage($week, $this->translator);
                $this->notifier->send($msg);
            }
        } catch (Throwable $exception) {
            $this->logger->error('Week could not be updated.', [
                'exceptionMessage' => $exception->getMessage(),
            ]);
        }
    }
}
