<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Event\WeekChangedEvent;
use App\Mealz\MealBundle\Service\CombinedMealService;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WeekChangedSubscriber implements EventSubscriberInterface
{
    private NotifierInterface $notifier;
    private CombinedMealService $combinedMealService;
    private TranslatorInterface $translator;

    public function __construct(
        NotifierInterface $notifier,
        CombinedMealService $combinedMealService,
        TranslatorInterface $translator
    ) {
        $this->notifier = $notifier;
        $this->combinedMealService = $combinedMealService;
        $this->translator = $translator;
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

        if ($event->doNotify()) {
            $this->sendWeekNotification($event->getWeek());
        }
    }

    private function sendWeekNotification(Week $week): void
    {
        $this->createNotificationMessage($week);

        //$this->notifier->sendAlert($message);
    }

    private function createNotificationMessage(Week $week): string
    {
        $header = $this->translator->trans('week.notification.header.default', [
            '%weekStart%' => $week->getStartTime()->format('d.m.'),
            '%weekEnd%' => $week->getEndTime()->format('d.m.'), ],
            'messages'
        );

        if ($week->isEnabled()) {
            $body = $this->addWeekToMessage($week);
            $footer = $this->translator->trans('week.notification.footer.default', [], 'messages');
        } else {
            $body = $this->translator->trans('week.notification.content.no_week', [], 'messages') . "\n";
            $footer = $this->translator->trans('week.notification.footer.no_week', [], 'messages');
        }

        return nl2br($header . "\n" . $body . "\n\n" . $footer);
    }

    private function addWeekToMessage(Week $week): string
    {
        $body = '';

        /** @var Day $day */
        foreach ($week->getDays() as $day) {
            $body .= $this->addDayToMessage($day);
        }

        return $body;
    }

    private function addDayToMessage(Day $day): string
    {
        $body = "\n" . $day . ': ';

        if (!$day->isEnabled()) {
            $body .= $this->translator->trans('week.notification.content.no_meals', [], 'messages');

            return $body;
        }

        $mealsOfTheDay = [];

        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            if ($meal->isCombinedMeal()) {
                continue;
            }
            $dish = $meal->getDish();

            if ($dish instanceof DishVariation) {
                $mealsOfTheDay[$dish->getParent()->getTitleEn()][] = $dish->getTitleEn();
            } else {
                if (!array_key_exists($dish->getTitleEn(), $mealsOfTheDay)) {
                    $mealsOfTheDay[$dish->getTitleEn()] = [];
                }
            }
        }

        if (0 == count($mealsOfTheDay)) {
            $body .= $this->translator->trans('week.notification.content.no_meals', [], 'messages');
        } else {
            $body .= $this->TwoDArrayToString($mealsOfTheDay);
        }

        return $body;
    }

    public function TwoDArrayToString(array $array): string
    {
        $result = '';

        /**
         * @var string $key
         * @var array  $value
         */
        foreach ($array as $key => $value) {
            $result .= $key;
            if (!(array_key_last($array) === $key)) {
                $result .= ', ';
            }
            if (!empty($value)) {
                $result .= '(' . implode(', ', $value) . ')';
            }
        }

        return $result;
    }
}
