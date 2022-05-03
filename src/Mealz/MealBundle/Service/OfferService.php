<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use DateTime;

class OfferService
{
    private ParticipantRepository $participantRepo;

    public function __construct(ParticipantRepository $participantRepo)
    {
        $this->participantRepo = $participantRepo;
    }

    public function getOffers(Meal $meal): array
    {
        $offers = [];
        /** @var Participant $participant */
        foreach ($meal->getParticipants() as $participant) {
            if (!$participant->isPending()) {
                continue;
            }

            if (!$meal->getDish()->isCombinedDish()) {
                $this->updateOfferCount($offers, $meal->getDish()->getSlug());
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
            $this->updateOfferCount($offers, $combinationID, array_values($dishes));
        }

        return $offers;
    }

    public function updateOfferCount(array &$offers, string $key, array $dishes = [])
    {
        if (isset($offers[$key])) {
            ++$offers[$key]['count'];
        } else {
            $offers[$key] = [
                'id' => $key,
                'count' => 1,
                'dishes' => (!empty($dishes) ? array_values($dishes) : $dishes),
            ];
        }
    }

    /**
     * Gets count of offered meals on a specific date.
     */
    public function getOfferCount(DateTime $date): int
    {
        return $this->participantRepo->getOfferCount($date);
    }

    /**
     * Gets count of offers for a specific meal.
     */
    public function getOfferCountByMeal(Meal $meal): int
    {
        return $this->participantRepo->getOfferCountByMeal($meal);
    }
}
