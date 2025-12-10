<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service\Mocks;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Override;

final class MealRepositoryMock implements MealRepositoryInterface
{
    public array $findOneByDateAndDishInputs = [];
    public ?Meal $outputFindOneByDateAndDish;
    public array $findAllOnInputs = [];
    public array $outputFindAllOn = [];
    public array $findAllBetweenInputs = [];
    public array $outputFindAllBetween = [];
    public array $outputMealsOnDayWithOptions = [];
    public array $outputFutureMeals = [];
    public array $outputOutdatedMeals = [];
    public array $outputLockedMeals = [];
    public array $futureMealsForDishInputs = [];
    public array $outputFutureMealsForDish = [];
    public array $findInputs = [];
    public mixed $outputFind;
    public array $outputFindAll = [];
    public array $findByCalls = [];
    public array $outputFindBy = [];
    public array $findOneByCriteria = [];
    public mixed $outputFindOneBy;
    public string $className = Meal::class;
    public array $matchingInputs = [];
    public ArrayCollection $outputMatching;

    #[Override]
    public function findOneByDateAndDish(DateTime $date, string $dish, array $userSelections = []): ?Meal
    {
        $this->findOneByDateAndDishInputs[] = [
            'date' => $date,
            'dish' => $dish,
            'userSelections' => $userSelections,
        ];

        return $this->outputFindOneByDateAndDish;
    }

    #[Override]
    public function findAllOn(DateTime $date): array
    {
        $this->findAllOnInputs[] = $date;

        return $this->outputFindAllOn;
    }

    #[Override]
    public function findAllBetween(DateTime $startDate, DateTime $endDate): array
    {
        $this->findAllBetweenInputs[] = [
            'start' => $startDate,
            'end' => $endDate,
        ];

        return $this->outputFindAllBetween;
    }

    #[Override]
    public function getMealsOnADayWithVariationOptions(): array
    {
        return $this->outputMealsOnDayWithOptions;
    }

    #[Override]
    public function getFutureMeals(): array
    {
        return $this->outputFutureMeals;
    }

    #[Override]
    public function getOutdatedMeals(): array
    {
        return $this->outputOutdatedMeals;
    }

    #[Override]
    public function getLockedMeals(): array
    {
        return $this->outputLockedMeals;
    }

    #[Override]
    public function getFutureMealsForDish(Dish $dish): array
    {
        $this->futureMealsForDishInputs[] = $dish;

        return $this->outputFutureMealsForDish;
    }

    #[Override]
    public function find($id)
    {
        $this->findInputs[] = $id;

        return $this->outputFind;
    }

    #[Override]
    public function findAll()
    {
        return $this->outputFindAll;
    }

    #[Override]
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
    {
        $this->findByCalls[] = [
            'criteria' => $criteria,
            'orderBy' => $orderBy,
            'limit' => $limit,
            'offset' => $offset,
        ];

        return $this->outputFindBy;
    }

    #[Override]
    public function findOneBy(array $criteria)
    {
        $this->findOneByCriteria[] = $criteria;

        return $this->outputFindOneBy;
    }

    #[Override]
    public function getClassName()
    {
        return $this->className;
    }

    #[Override]
    public function matching(Criteria $criteria)
    {
        $this->matchingInputs[] = $criteria;

        return $this->outputMatching;
    }
}
