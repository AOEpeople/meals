<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Dish;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;

interface DishRepositoryInterface extends ObjectRepository
{
    public function getSortedDishesQueryBuilder(array $options = []): QueryBuilder;

    public function hasDishAssociatedMeals(Dish $dish): bool;

    /**
     * Counts the number of Dish was taken in the last X Weeks.
     */
    public function countNumberDishWasTaken(Dish $dish, string $countPeriod): int;
}
