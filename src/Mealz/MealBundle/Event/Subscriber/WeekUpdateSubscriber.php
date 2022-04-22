<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\WeekUpdateEvent;
use App\Mealz\MealBundle\Service\CombinedMealService;
use App\Mealz\MealBundle\Service\Notification\MealsNotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WeekUpdateSubscriber implements EventSubscriberInterface
{
    private MealsNotificationService $notificationService;
    private CombinedMealService $combinedMealService;

    public function __construct(
        MealsNotificationService $notificationService,
        CombinedMealService $combinedMealService
    ) {
        $this->notificationService = $notificationService;
        $this->combinedMealService = $combinedMealService;
    }

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
            $this->notificationService->sendWeeklyMenuUpdate($week);
        }
    }
}
