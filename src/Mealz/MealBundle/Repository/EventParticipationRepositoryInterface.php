<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Entity\EventParticipation;
use Doctrine\Persistence\ObjectRepository;

interface EventParticipationRepositoryInterface extends ObjectRepository
{
    public function add(EventParticipation $eventParticipation): void;

    public function findByEventAndDay(Day $day, Event $event): ?EventParticipation;
}
