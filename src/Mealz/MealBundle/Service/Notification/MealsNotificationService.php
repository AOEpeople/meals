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
        $msg = $this->getMenuUpdateNotification($week);
        $this->notifier->sendAlert($msg);
    }

    private function getMenuUpdateNotification(Week $week): string
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
            $body = $this->getOfferedDishesByWeek($week);
            $footer = $this->translator->trans('week.notification.footer.default', [], 'messages');
        }

        return $header . "\n" . $body . "\n\n" . $footer;
    }

    private function getOfferedDishesByWeek(Week $week): string
    {
        $body = [];

        foreach ($week->getDays() as $day) {
            $body[] = $this->getOfferedDishesByDay($day);
        }

        return "\n" . implode("\n", $body);
    }

    private function getOfferedDishesByDay(Day $day): string
    {
        $body = $day->getDateTime()->format('l') . ': ';

        if (!$day->isEnabled() || (0 === count($day->getMeals()))) {
            return $body . $this->translator->trans('week.notification.content.no_meals', [], 'messages');
        }

        $offeredDishes = [];

        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            if ($meal->isCombinedMeal()) {
                continue;
            }

            $dish = $meal->getDish();
            if ($dish instanceof DishVariation) {
                $offeredDishes[$dish->getParent()->getTitleEn()][] = $dish->getTitleEn();
            } else {
                $offeredDishes[$dish->getTitleEn()] = [];
            }
        }

        return $body . $this->toString($offeredDishes);
    }

    /**
     * @param array<string, list<string>> $dishes
     */
    private function toString(array $dishes): string
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
