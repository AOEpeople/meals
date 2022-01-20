<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;

class OfferService
{
    public static function getOffers(Meal $meal): array
    {
        $offers = [];
        /** @var Participant $participant */
        foreach ($meal->getParticipants() as $participant) {
            if (!$participant->isPending()) {
                continue;
            }

            $dishes = [];
            $combinedDishes = $participant->getCombinedDishes();
            /** @var Dish $dish */
            foreach ($combinedDishes as $dish) {
                $dishes[$dish->getSlug()] = [
                    'slug' => $dish->getSlug(),
                    'title' => $dish->getTitle(),
                ];
            }

            $dishSlugs = array_keys($dishes);
            sort($dishSlugs, SORT_NATURAL);
            $combinationID = implode(',', $dishSlugs);
            if (isset($offers[$combinationID])) {
                ++$offers[$combinationID]['count'];
            } else {
                $offers[$combinationID] = [
                    'id' => $combinationID,
                    'count' => 1,
                    'dishes' => array_values($dishes),
                ];
            }
        }

        return $offers;
    }
}
