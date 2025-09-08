<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Helper;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\MealBundle\Repository\EventPartRepo;
use App\Mealz\MealBundle\Repository\EventRepository;
use App\Mealz\MealBundle\Repository\MealRepository;
use App\Mealz\MealBundle\Service\EventParticipationService;
use Exception;

final class MealAdminHelper
{
    public function __construct(
        private readonly EventParticipationService $eventService,
        private readonly EventRepository $eventRepository,
        private readonly EventPartRepo $eventPartRepo,
        private readonly DishRepository $dishRepository,
        private readonly MealRepository $mealRepository
    ) {
    }

    public function setParticipationLimit(Meal $mealEntity, array $meal): void
    {
        if (
            true === isset($meal['participationLimit'])
            && 0 < $meal['participationLimit']
            && count($mealEntity->getParticipants()) <= $meal['participationLimit']
        ) {
            $mealEntity->setParticipationLimit($meal['participationLimit']);
        } else {
            $mealEntity->setParticipationLimit(0);
        }
    }

    public function findEvent(int $eventId): ?Event
    {
        return $this->eventRepository->find($eventId);
    }

    public function checkIfEventExistsForDay(int $eventId, Day $day): bool
    {
        if ($this->eventPartRepo->findByEventIdAndDay($day, $eventId)) {
            return true;
        }

        return false;
    }

    public function handleMealArray(array $mealArr, Day $dayEntity): void
    {
        foreach ($mealArr as $meal) {
            if (false === isset($meal['dishSlug'])) {
                continue;
            }
            $dishEntity = $this->dishRepository->findOneBy(['slug' => $meal['dishSlug']]);
            if (null === $dishEntity) {
                throw new Exception('107: dish not found for slug: ' . $meal['dishSlug']);
            }
            // if mealId is null create meal
            if (false === isset($meal['mealId'])) {
                $this->createMeal($dishEntity, $dayEntity, $meal);
            } else {
                $this->modifyMeal($meal, $dishEntity, $dayEntity);
            }
        }
    }

    private function createMeal(Dish $dishEntity, Day $dayEntity, array $meal): void
    {
        $mealEntity = new Meal($dishEntity, $dayEntity);
        $mealEntity->setPrice($dishEntity->getPrice());
        $this->setParticipationLimit($mealEntity, $meal);
        $dayEntity->addMeal($mealEntity);
    }

    /**
     * @throws Exception
     */
    private function modifyMeal(array $meal, Dish $dishEntity, Day $dayEntity): void
    {
        $mealEntity = $this->mealRepository->find($meal['mealId']);
        if (null === $mealEntity) {
            // this happens because meals without participations are deleted, even though they
            // could be modified later on (this shouldn't happen but might)
            $mealEntity = new Meal($dishEntity, $dayEntity);
            $mealEntity->setPrice($dishEntity->getPrice());
            $dayEntity->addMeal($mealEntity);

            return;
        }

        $this->setParticipationLimit($mealEntity, $meal);
        // check if the requested dish is the same as before
        if ($mealEntity->getDish()->getId() === $dishEntity->getId()) {
            return;
        }

        // check if meal already exists and can be modified (aka has no participations)
        if (!$mealEntity->hasParticipations()) {
            $mealEntity->setDish($dishEntity);
            $mealEntity->setPrice($dishEntity->getPrice());

            return;
        }

        throw new Exception('108: meal has participations for id: ' . $meal['mealId']);
    }
}
