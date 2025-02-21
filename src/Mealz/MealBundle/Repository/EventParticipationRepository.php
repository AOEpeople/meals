<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Entity\EventParticipation;
use Override;

/**
 * @extends BaseRepository<int, EventParticipation>
 */
final class EventParticipationRepository extends BaseRepository implements EventParticipationRepositoryInterface
{
    #[Override]
    public function add($eventParticipation): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($eventParticipation);
        $entityManager->flush();
    }

    #[Override]
    public function findByEventAndDay(Day $day, Event $event): ?EventParticipation
    {
        $queryBuilder = $this->createQueryBuilder('m');

        // WHERE
        $queryBuilder->andWhere('m.day = :day');
        $queryBuilder->andWhere('m.event = :event');
        $queryBuilder->setParameter('day', $day->getId());
        $queryBuilder->setParameter('event', $event->getId());

        $result = $queryBuilder->getQuery()->getResult();

        return count($result) ? $result[0] : null;
    }
}
