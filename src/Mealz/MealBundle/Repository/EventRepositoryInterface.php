<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;

interface EventRepositoryInterface extends ObjectRepository
{
    public function getSortedEventsQueryBuilder(): QueryBuilder;
}
