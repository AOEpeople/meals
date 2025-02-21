<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Event;

/**
 * @extends BaseRepository<int, Event>
 */
final class EventRepository extends BaseRepository implements EventRepositoryInterface
{
}
