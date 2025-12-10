<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service\Mocks;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Repository\DishRepositoryInterface;
use Doctrine\ORM\QueryBuilder;
use Override;

final class DishRepositoryMock implements DishRepositoryInterface
{
    public array $sortedDishesOptions = [];
    public QueryBuilder $outputQueryBuilder;
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

    #[Override]
    public function getSortedDishesQueryBuilder(array $options = []): QueryBuilder
    {
        $this->sortedDishesOptions[] = $options;

        return $this->outputQueryBuilder;
    }

    #[Override]
    public function hasDishAssociatedMeals(Dish $dish): bool
    {
        $this->hasDishAssociatedMealsInputs[] = $dish;

        return $this->outputHasDishAssociatedMeals;
    }

    #[Override]
    public function hasDishAssociatedCombiMealsInFuture(Dish $dish): bool
    {
        $this->hasDishAssociatedCombiFutureInputs[] = $dish;

        return $this->outputHasDishAssociatedCombiFuture;
    }

    #[Override]
    public function countNumberDishWasTaken(Dish $dish, string $countPeriod): int
    {
        $this->countDishTakenInputs[] = [
            'dish' => $dish,
            'countPeriod' => $countPeriod,
        ];

        return $this->outputCountDishTaken;
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
}
