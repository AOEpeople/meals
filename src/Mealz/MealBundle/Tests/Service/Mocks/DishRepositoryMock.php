<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service\Mocks;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Repository\DishRepositoryInterface;
use Doctrine\ORM\QueryBuilder;

final class DishRepositoryMock implements DishRepositoryInterface
{
    public array $sortedDishesOptions = [];
    public ?QueryBuilder $outputQueryBuilder = null;
    public array $hasDishAssociatedMealsInputs = [];
    public bool $outputHasDishAssociatedMeals = false;
    public array $hasDishAssociatedCombiFutureInputs = [];
    public bool $outputHasDishAssociatedCombiFuture = false;
    public array $countDishTakenInputs = [];
    public int $outputCountDishTaken = 0;
    public array $findInputs = [];
    public mixed $outputFind;
    public array $outputFindAll = [];
    public array $findByCalls = [];
    public array $outputFindBy = [];
    public array $findOneByCriteria = [];
    public mixed $outputFindOneBy;
    public string $className = Dish::class;

    public function getSortedDishesQueryBuilder(array $options = []): QueryBuilder
    {
        $this->sortedDishesOptions[] = $options;

        return $this->outputQueryBuilder;
    }

    public function hasDishAssociatedMeals(Dish $dish): bool
    {
        $this->hasDishAssociatedMealsInputs[] = $dish;

        return $this->outputHasDishAssociatedMeals;
    }

    public function hasDishAssociatedCombiMealsInFuture(Dish $dish): bool
    {
        $this->hasDishAssociatedCombiFutureInputs[] = $dish;

        return $this->outputHasDishAssociatedCombiFuture;
    }

    public function countNumberDishWasTaken(Dish $dish, string $countPeriod): int
    {
        $this->countDishTakenInputs[] = [
            'dish' => $dish,
            'countPeriod' => $countPeriod,
        ];

        return $this->outputCountDishTaken;
    }

    public function find($id)
    {
        $this->findInputs[] = $id;

        return $this->outputFind;
    }

    public function findAll()
    {
        return $this->outputFindAll;
    }

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

    public function findOneBy(array $criteria)
    {
        $this->findOneByCriteria[] = $criteria;

        return $this->outputFindOneBy;
    }

    public function getClassName()
    {
        return $this->className;
    }
}
