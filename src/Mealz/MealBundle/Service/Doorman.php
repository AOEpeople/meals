<?php


namespace Mealz\MealBundle\Service;
use Mealz\MealBundle\Entity\Meal;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * central business logic to determine if the currently logged in user is allowed to do a certain action
 *
 * For instance a user should not be allowed to register for a meal, that
 * starts in 10 minutes, but maybe a user with a special role ("cook") should be able
 * to do that even after the meal has started. The time for registration could also be
 * limited by the meal itself (something special has to be ordered a day earlier) or the number
 * of available meals could be limited.
 *
 * This logic should be accessible in controllers, templates and services.
 */
class Doorman {

	/**
	 * @var \DateTime
	 */
	protected $now;

	/**
	 * @var SecurityContext
	 */
	protected $securityContext;

	public function __construct(SecurityContext $securityContext, $lockToggleParticipationAt = '-1 day 12:00') {
		$this->securityContext = $securityContext;
		$this->now = time();
		$this->lockToggleParticipationAt = $lockToggleParticipationAt;
	}

	public function isUserAllowedToJoin(Meal $meal) {
		if ($this->isKitchenStaff()) {
			return TRUE;
		}
		if(!$this->securityContext->getToken()->getUser()->getProfile() instanceof Profile || $meal->isLimitReached()) {
			return FALSE;
		}
		return $this->isToggleParticipationAllowed($meal->getDateTime());
	}

	public function isUserAllowedToLeave(Meal $meal) {
		if($this->isKitchenStaff()) {
			return TRUE;
		}
		if(!$this->securityContext->getToken()->getUser()->getProfile() instanceof Profile) {
			return FALSE;
		}

		return $this->isToggleParticipationAllowed($meal->getDateTime());
	}

	public function isKitchenStaff() {
		return $this->securityContext->isGranted('ROLE_KITCHEN_STAFF');
	}

	public function isUserAllowedToAddGuest(Meal $meal) {
		// @TODO: add a separate role for that
		return $this->isKitchenStaff() || $this->isUserAllowedToJoin($meal);
	}

	public function isUserAllowedToRemoveGuest(Meal $meal) {
		// @TODO: add a separate role for that
		return $this->isKitchenStaff() || $this->isUserAllowedToLeave($meal);
	}

	public function isUserAllowedToRequestCostAbsorption(Meal $meal) {
		// @TODO: add a separate role for that
		return $this->isKitchenStaff() || $this->isUserAllowedToAddGuest($meal);
	}

	public function isToggleParticipationAllowed(\DateTime $mealDateTime)
	{
		$date = clone($mealDateTime);
		$date->modify($this->lockToggleParticipationAt);

		if ($date->getTimestamp() > $this->now) {
			// if: meal is in mealDateTime + $lockToggleParticipationAt (for instance meal time -1 day)
			return TRUE;
		}

		return FALSE;
	}
}