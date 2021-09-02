<?php

namespace App\Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use App\Mealz\UserBundle\Entity\Profile;

/**
 * Guest invitation repository
 *
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class GuestInvitationRepository extends EntityRepository
{
    /**
     * Gets the guest invitation from a particular user on a particular day.
     *
     * @param  Profile $host
     * @param  Day $day
     * @return GuestInvitation
     */
    public function findOrCreateInvitation(Profile $host, Day $day)
    {
        $invitation = parent::findOneBy(['host' => $host->getUsername(), 'day' => $day->getId()]);

        if (($invitation instanceof GuestInvitation) === false) {
            $invitation = new GuestInvitation($host, $day);

            /** @var \Doctrine\ORM\EntityManager $entityManager */
            $entityManager = $this->getEntityManager();
            $entityManager->persist($invitation);
            $entityManager->flush();
        }

        return $invitation;
    }
}
