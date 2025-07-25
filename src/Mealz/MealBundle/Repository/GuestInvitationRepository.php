<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Entity\GuestInvitation;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

/**
 * @extends BaseRepository<int, GuestInvitation>
 */
class GuestInvitationRepository extends BaseRepository implements GuestInvitationRepositoryInterface
{
    /**
     * Gets the event guest invitation from a particular user on a particular day.
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function findOrCreateEventInvitation(Profile $host, Day $day, ?EventParticipation $eventParticipation): GuestInvitation
    {
        $entityManager = $this->getEntityManager();
        if ($eventParticipation) {
            $invitation = $this->findOneBy(['host' => $host->getUsername(), 'day' => $day->getId(), 'eventParticipation' => $eventParticipation]);
        }

        if (($invitation instanceof GuestInvitation) === false) {
            $invitation = new GuestInvitation($host, $day, $eventParticipation);
            $entityManager->persist($invitation);
            $entityManager->flush();
        }

        return $invitation;
    }

    /**
     * Gets the guest invitation from a particular user on a particular day.
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function findOrCreateInvitation(Profile $host, Day $day): GuestInvitation
    {
        $entityManager = $this->getEntityManager();
        $invitation = $this->findOneBy(['host' => $host->getUsername(), 'day' => $day->getId()]);

        if (($invitation instanceof GuestInvitation) === false) {
            $invitation = new GuestInvitation($host, $day, null);
            $entityManager->persist($invitation);
            $entityManager->flush();
        }

        return $invitation;
    }
}
