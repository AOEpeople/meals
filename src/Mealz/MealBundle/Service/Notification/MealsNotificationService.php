<?php

namespace App\Mealz\MealBundle\Service\Notification;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use Symfony\Contracts\Translation\TranslatorInterface;

class MealsNotificationService
{
    private NotifierInterface $notifier;
    private TranslatorInterface $translator;

    public function __construct(
        NotifierInterface $notifier,
        TranslatorInterface $translator
    ) {
        $this->notifier = $notifier;
        $this->translator = $translator;
    }

    public function sendMattermostNotification(string $message): void
    {
        $this->notifier->sendAlert($message);
    }

    public function createWeekNotification(Week $week): string
    {
        $header = $this->translator->trans(
            'week.notification.header.no_week',
            [
                '%weekStart%' => $week->getStartTime()->format('d.m.'),
                '%weekEnd%' => $week->getEndTime()->format('d.m.'),
            ],
            'messages'
        );
        $body = '';
        $footer = '';

        if ($week->isEnabled()) {
            $header = '#### ' . $this->translator->trans(
                    'week.notification.header.default',
                    [
                        '%weekStart%' => $week->getStartTime()->format('d.m.'),
                        '%weekEnd%' => $week->getEndTime()->format('d.m.'),
                    ],
                    'messages'
                );
            $body = $this->addWeekToMessage($week);
            $footer = $this->translator->trans('week.notification.footer.default', [], 'messages');
        }

        return $header . "\n" . $body . "\n\n" . $footer;
    }

    private function addWeekToMessage(Week $week): string
    {
        $body = "\n";

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
                $mealsOfTheDay[$dish->getTitleEn()] = [];
            }
        }

        if (empty($mealsOfTheDay)) {
            $body .= $this->translator->trans('week.notification.content.no_meals', [], 'messages');
        } else {
            $body .= $this->nestedArrayToString($mealsOfTheDay);
        }

        return $body;
    }

    private function nestedArrayToString(array $array): string
    {
        $result = '';

        /**
         * @var string $key
         * @var array  $value
         */
        foreach ($array as $key => $value) {
            $result .= '**' . $key . '**';
            if (array_key_last($array) !== $key) {
                $result .= ', ';
            }
            if (!empty($value)) {
                $result .= ' (' . implode(', ', $value) . ')';
            }
        }

        return $result;
    }
}
