<?php

declare(strict_types=1);

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

    public function sendWeeklyMenuUpdate(Week $week): void
    {
        $msg = $this->createWeekNotification($week);
        $this->notifier->sendAlert($msg);
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
        $body = [];

        /** @var Day $day */
        foreach ($week->getDays() as $day) {
            $body[] = $this->addDayToMessage($day);
        }

        return "\n" . implode("\n", $body);
    }

    private function addDayToMessage(Day $day): string
    {
        $body = $day->getDateTime()->format('l') . ': ';

        if (!$day->isEnabled()) {
            $body .= $this->translator->trans('week.notification.content.no_meals', [], 'messages');

            return $body;
        }

        $dayMeals = $day->getMeals();
        if (0 === count($dayMeals)) {
            return $body . $this->translator->trans('week.notification.content.no_meals', [], 'messages');
        }

        $mealsOfTheDay = [];

        /** @var Meal $meal */
        foreach ($dayMeals as $meal) {
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

        $body .= $this->nestedArrayToString($mealsOfTheDay);

        return $body;
    }

    /**
     * @param array<string, array> $dishes
     */
    private function nestedArrayToString(array $dishes): string
    {
        $result = [];

        foreach ($dishes as $dishTitle => $dishVarTitles) {
            $result[$dishTitle] = '**' . $dishTitle . '**';

            if (!empty($dishVarTitles)) {
                $result[$dishTitle] .= sprintf(' (%s)', implode(', ', $dishVarTitles));
            }
        }

        return implode(', ', $result);
    }
}
