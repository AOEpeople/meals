<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\WeekUpdateEvent;
use App\Mealz\MealBundle\Message\WeeklyMenuMessage;
use App\Mealz\MealBundle\Service\CombinedMealService;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WeekUpdateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CombinedMealService $combinedMealService,
        // Weekly menu notifier
        private readonly NotifierInterface $notifier,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @return string[]
     *
     * @psalm-return array{WeekUpdateEvent::class: 'onWeekUpdate'}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WeekUpdateEvent::class => 'onWeekUpdate',
        ];
    }

    public function onWeekUpdate(WeekUpdateEvent $event): void
    {
        $week = $event->getWeek();
        $this->combinedMealService->update($week);

        if ($event->getNotify()) {
            $msg = new WeeklyMenuMessage($week, $this->translator);
            $this->notifier->send($msg);
        }
    }
}
