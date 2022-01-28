<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\WeekChangedEvent;
use App\Mealz\MealBundle\Service\CombinedMealService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CombinedMealSubscriber implements EventSubscriberInterface
{
    private CombinedMealService $combinedMealService;

    public function __construct(CombinedMealService $combinedMealService)
    {
        $this->combinedMealService = $combinedMealService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WeekChangedEvent::class => 'onWeekChanged',
        ];
    }

    public function onWeekChanged(WeekChangedEvent $event): void
    {
        $this->combinedMealService->update($event->getWeek());
    }
}
