<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use Doctrine\ORM\QueryBuilder;

class EventRepository extends BaseRepository implements EventRepositoryInterface
{
    public function getSortedEventsQueryBuilder(): QueryBuilder
    {
        $query = $this->createQueryBuilder('d');

        $query->orderBy('d.title', 'DESC');

        return $query;
    }
}
