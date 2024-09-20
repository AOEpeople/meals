<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Entity\EventParticipation;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template-extends ObjectRepository<EventParticipation>
 */
interface EventPartRepoInterface extends ObjectRepository
{
    public function add($eventParticipation): void;

    public function findByEventAndDay(Day $day, Event $event): ?EventParticipation;
}
