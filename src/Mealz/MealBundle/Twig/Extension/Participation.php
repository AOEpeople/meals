<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Twig\Extension;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Participation extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('weeklyMenu', [$this, 'getWeeklyMenu']),
            new TwigFunction('isParticipant', [$this, 'isParticipant']),
        ];
    }

    public function getWeeklyMenu(Week $week): array
    {
        $menu = [];

        /** @var Day $day */
        foreach ($week->getDays() as $day) {
            $menu[$day->getDateTime()->format('Y-m-d')] = $this->getDailyMenu($day);
        }

        return $menu;
    }

    private function getDailyMenu(Day $day): array
    {
        $dishes = [];

        foreach ($day->getMeals() as $meal) {
            $dish = $meal->getDish();

            if ($dish instanceof DishVariation) {
                $parentDish = $dish->getParent();
                if (null === $parentDish) {
                    throw new RuntimeException('invalid dish variation; parent dish not found, dishId: ' . $dish->getId());
                }

                $parentDishId = $parentDish->getId();
                if (!isset($dishes[$parentDishId])) {
                    $dishes[$parentDishId] = [
                        'title' => $parentDish->getTitle(),
                        'slug' => $parentDish->getSlug(),
                        'isCombined' => false,  // a combined dish doesn't have variations relation
                    ];
                }

                $dishes[$parentDishId]['variations'][] = [
                    'title' => $dish->getTitle(),
                    'slug' => $dish->getSlug(),
                ];
            } else {
                $dishes[] = [
                    'title' => $dish->getTitle(),
                    'slug' => $dish->getSlug(),
                    'variations' => [],
                    'isCombined' => $dish->isCombinedDish(),
                ];
            }
        }

        return array_values($dishes);
    }

    /**
     * @param Participant[] $userParticipations
     */
    public function isParticipant(array $userParticipations, Collection $mealParticipations)
    {
        foreach ($userParticipations as $participation) {
            if ($mealParticipations->contains($participation)) {
                return $participation;
            }
        }

        return null;
    }
}
