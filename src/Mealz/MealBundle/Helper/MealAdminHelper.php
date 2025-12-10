<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Helper;

use App\Mealz\AccountingBundle\Entity\Price;
use App\Mealz\AccountingBundle\Repository\PriceRepositoryInterface;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Helper\Exceptions\PriceNotFoundException;
use App\Mealz\MealBundle\Repository\DishRepositoryInterface;
use App\Mealz\MealBundle\Repository\EventPartRepoInterface;
use App\Mealz\MealBundle\Repository\EventRepositoryInterface;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use Exception;
use Psr\Log\LoggerInterface;

final class MealAdminHelper
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
        private readonly EventPartRepoInterface   $eventPartRepo,
        private readonly DishRepositoryInterface  $dishRepository,
        private readonly MealRepositoryInterface  $mealRepository,
        private readonly PriceRepositoryInterface $priceRepository,
        private readonly LoggerInterface          $logger
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

    /**
     * @throws PriceNotFoundException
     */
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
            $dateTime = new \DateTimeImmutable('now');
            $dateTimeYearAsInt = (int)$dateTime->format('Y');
            $price = $this->priceRepository->findByYear($dateTimeYearAsInt);
            if (!($price instanceof Price)) {
                $this->logger->error('Prices could not be loaded by price repository in handleMealArray.', [
                    'year' => $dateTimeYearAsInt,
                ]);
                throw PriceNotFoundException::isNotFound($dateTimeYearAsInt);
            }
            // if mealId is null create meal
            if (false === isset($meal['mealId'])) {
                $this->createMeal($dishEntity, $dayEntity, $meal, $price);
            } else {
                $this->modifyMeal($meal, $dishEntity, $dayEntity, $price);
            }
        }
    }

    private function createMeal(Dish $dishEntity, Day $dayEntity, array $meal, Price $price): void
    {
        $mealEntity = new Meal($dishEntity, $price, $dayEntity);
        $this->setParticipationLimit($mealEntity, $meal);
        $dayEntity->addMeal($mealEntity);
    }

    /**
     * @throws Exception
     */
    private function modifyMeal(array $meal, Dish $dishEntity, Day $dayEntity, $price): void
    {
        $mealEntity = $this->mealRepository->find($meal['mealId']);
        if (null === $mealEntity) {
            // this happens because meals without participations are deleted, even though they
            // could be modified later on (this shouldn't happen but might)
            $mealEntity = new Meal($dishEntity, $price, $dayEntity);
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

            return;
        }

        throw new Exception('108: meal has participations for id: ' . $meal['mealId']);
    }
}
