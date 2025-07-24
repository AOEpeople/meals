<?php

namespace App\Mealz\MealBundle\EventListener;

use App\Mealz\MealBundle\Entity\Participant;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * listener that ensures that there won't be duplicate entries for the same participant in the database.
 */
class ParticipantPersistenceListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();

        if ($entity instanceof Participant) {
            $this->checkUniqueParticipant($entity, $entityManager);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();

        if ($entity instanceof Participant) {
            $this->checkUniqueParticipant($entity, $entityManager);
        }
    }

    private function checkUniqueParticipant(Participant $participant, EntityManager $entityManager): void
    {
        if ($this->participantExists($participant, $entityManager)) {
            throw new ParticipantNotUniqueException('This participant has already joined: ' . $participant);
        }
    }

    private function participantExists(Participant $participant, EntityManager $entityManager): bool
    {
        $queryBuilder = $entityManager->createQueryBuilder();

        if (null !== $participant->getMeal()) {
            $query = $this->buildQueryMealParticipantExists($participant, $queryBuilder);
        } elseif (null !== $participant->getEventParticipation()) {
            $query = $this->buildQueryEventParticipantExists($participant, $queryBuilder);
        } else {
            return false;
        }

        return $query->execute(null, AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    private function buildQueryMealParticipantExists(Participant $participant, QueryBuilder $queryBuilder): Query
    {
        $queryBuilder
            ->select('COUNT(p.id)')
            ->from(Participant::class, 'p')
            ->join('p.meal', 'm')
            ->join('p.profile', 'u')
            ->where('m = :meal AND u = :profile')
        ;
        if ($participant->getId()) {
            $queryBuilder->andWhere('p.id != :id');
            $queryBuilder->setParameter('id', $participant->getId());
        }

        $query = $queryBuilder->getQuery();
        $query->setParameter('meal', $participant->getMeal()->getId());
        $query->setParameter('profile', $participant->getProfile()->getUsername());
        $query->disableResultCache();

        return $query;
    }

    private function buildQueryEventParticipantExists(Participant $participant, QueryBuilder $queryBuilder): Query
    {
        $queryBuilder
            ->select('COUNT(p.id)')
            ->from(Participant::class, 'p')
            ->join('p.event_participation', 'e')
            ->join('p.profile', 'u')
            ->where('e = :event_participation AND u = :profile')
        ;
        if ($participant->getId()) {
            $queryBuilder->andWhere('p.id != :id');
            $queryBuilder->setParameter('id', $participant->getId());
        }

        $query = $queryBuilder->getQuery();
        $query->setParameter('event_participation', $participant->getEventParticipation()->getId());
        $query->setParameter('profile', $participant->getProfile()->getUsername());
        $query->disableResultCache();

        return $query;
    }
}
