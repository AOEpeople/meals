<?php

namespace App\Mealz\MealBundle\EventListener;

use App\Mealz\MealBundle\Entity\Participant;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use RuntimeException;

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
        if ($entityManager->getConnection()->getTransactionNestingLevel() < 1) {
            throw new RuntimeException(sprintf('Participants can only be updated inside a transaction to ensure consistency. See http://docs.doctrine-project.org/en/latest/reference/transactions-and-concurrency.html#approach-2-explicitly'));
        }
        if ($this->participantExists($participant, $entityManager)) {
            throw new ParticipantNotUniqueException('This participant has already joined: ' . $participant);
        }
    }

    private function participantExists(Participant $participant, EntityManager $entityManager): bool
    {
        $queryBuilder = $entityManager->createQueryBuilder();

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

        return $query->execute(null, AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }
}
