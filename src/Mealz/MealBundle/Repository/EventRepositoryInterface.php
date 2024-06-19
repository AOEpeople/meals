<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Event;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template-extends ObjectRepository<Event>
 */
interface EventRepositoryInterface extends ObjectRepository
{
}
