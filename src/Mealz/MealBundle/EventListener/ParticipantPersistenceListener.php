<?php

namespace App\Mealz\MealBundle\EventListener;

use App\Mealz\MealBundle\Entity\Participant;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * listener that ensures that there won't be duplicate entries for the same participant in the database.
 */
class ParticipantPersistenceListener
{
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof Participant) {
            $this->checkUniqueParticipant($entity, $entityManager);
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

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
            $queryBuilder
                ->select('COUNT(p.id)')
                ->from('MealzMealBundle:Participant', 'p')
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
            $query->useResultCache(false);

        } elseif (null !== $participant->getEvent()) {
            $queryBuilder
                ->select('COUNT(p.id)')
                ->from('MealzMealBundle:Participant', 'p')
                ->join('p.event', 'e')
                ->join('p.profile', 'u')
                ->where('e = :event AND u = :profile')
            ;
            if ($participant->getId()) {
                $queryBuilder->andWhere('p.id != :id');
                $queryBuilder->setParameter('id', $participant->getId());
            }

            $query = $queryBuilder->getQuery();
            $query->setParameter('event', $participant->getEvent()->getId());
            $query->setParameter('profile', $participant->getProfile()->getUsername());
            $query->useResultCache(false);
        } else {
            return false;
        }

        return $query->execute(null, AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }
}
