<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Mealz\UserBundle\Entity\Profile;

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

		if (!($invitation instanceof GuestInvitation)) {
			$invitation = new GuestInvitation($host, $day);

			/** @var \Doctrine\ORM\EntityManager $em */
			$em = $this->getEntityManager();
			$em->persist($invitation);
			$em->flush();
		}

		return $invitation;
	}
}
