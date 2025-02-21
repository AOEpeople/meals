<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Message;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Service\Notification\MessageInterface;
use Override;
use Symfony\Contracts\Translation\TranslatorInterface;

final class WeeklyMenuMessage implements MessageInterface
{
    private Week $week;
    private TranslatorInterface $translator;

    public function __construct(Week $week, TranslatorInterface $translator)
    {
        $this->week = $week;
        $this->translator = $translator;
    }

    #[Override]
    public function getContent(): string
    {
        $header = $this->translator->trans(
            'week.notification.header.no_week',
            [
                '%weekStart%' => $this->week->getStartTime()->format('d.m.'),
                '%weekEnd%' => $this->week->getEndTime()->format('d.m.'),
            ],
            'messages'
        );
        $tableHeader = '';
        $body = '';
        $footer = '';

        if ($this->week->isEnabled()) {
            $header = '#### ' . $this->translator->trans(
                'week.notification.header.default',
                [
                    '%weekStart%' => $this->week->getStartTime()->format('d.m.'),
                    '%weekEnd%' => $this->week->getEndTime()->format('d.m.'),
                ],
                'messages'
            );
            $tableHeader = "\n|Day|Meals|\n|:-----|:-----|\n";
            $body = $this->getDishesByWeek($this->week);
            $footer = $this->translator->trans('week.notification.footer.default', [], 'messages');
        }

        return $header . $tableHeader . $body . "\n\n" . $footer;
    }

    /**
     * given a Week returns a formatted string of all dishes that are being offered on the menu for the week.
     */
    private function getDishesByWeek(Week $week): string
    {
        $body = [];

        foreach ($week->getDays() as $day) {
            $body[] = $this->getDishesByDay($day);
        }

        return '| ' . implode("\n| ", $body);
    }

    /**
     * given a Day returns a formatted string of all dishes that are being offered on the menu for the day.
     */
    private function getDishesByDay(Day $day): string
    {
        $body = $day->getDateTime()->format('l') . ' | ';

        if (!$day->isEnabled() || (0 === count($day->getMeals()))) {
            return $body . $this->translator->trans('week.notification.content.no_meals', [], 'messages') . ' | ';
        }

        $dishes = [];

        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            if ($meal->isCombinedMeal()) {
                continue;
            }

            $dish = $meal->getDish();
            $parentDish = $dish->getParent();

            if (null !== $parentDish) {
                $dishes[$parentDish->getTitleEn()][] = $dish->getTitleEn();
            } else {
                $dishes[$dish->getTitleEn()] = [];
            }
        }

        return $body . $this->toString($dishes);
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

        return implode(', ', $result) . ' |';
    }
}
