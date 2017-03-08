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
	 * Doorman constants defining access types
	 * @see $this->hasAccessTo
	 */
	const AT_MEAL_PARTICIPATION = 0;

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

	/**
	 * @param Meal $meal
	 * @return bool
	 */
	public function isUserAllowedToJoin(Meal $meal) {
		return $this->hasAccessTo(self::AT_MEAL_PARTICIPATION,['meal'=>$meal]);
	}

	/**
	 * @param Meal $meal
	 * @return bool
	 */
	public function isUserAllowedToLeave(Meal $meal) {
		return $this->hasAccessTo(self::AT_MEAL_PARTICIPATION,['meal'=>$meal]);
	}

	/**
	 * @return bool
	 */
	public function isKitchenStaff() {
		return $this->securityContext->isGranted('ROLE_KITCHEN_STAFF');
	}

	/**
	 * @param Meal $meal
	 * @return bool
	 */
	public function isUserAllowedToAddGuest(Meal $meal) {
		// @TODO: add a separate role for that
		return $this->isKitchenStaff() || $this->isUserAllowedToJoin($meal);
	}

	/**
	 * @param Meal $meal
	 * @return bool
	 */
	public function isUserAllowedToRemoveGuest(Meal $meal) {
		// @TODO: add a separate role for that
		return $this->isKitchenStaff() || $this->isUserAllowedToLeave($meal);
	}

	public function isUserAllowedToRequestCostAbsorption(Meal $meal) {
		// @TODO: add a separate role for that
		return $this->isKitchenStaff() || $this->isUserAllowedToAddGuest($meal);
	}

	/**
	 * @param \DateTime $lockParticipationDateTime
	 * @return bool
	 */
	public function isToggleParticipationAllowed(\DateTime $lockParticipationDateTime)
	{
		// is it still allowed to participate in the meal by now?
		return ($lockParticipationDateTime->getTimestamp() > $this->now);
	}

	/**
	 * Checking access to a vary of processes inside of meals.
	 * Accesstype is a constant of class Doorman. Use this to tell the method what to check ;-)
	 * To be used in future to add more acces checks.
	 *
	 * @param integer $accesstype		What access shall be checked
	 * @param array $params
	 * @return bool
	 */
	private function hasAccessTo($accesstype, $params = []) {
			// admins always have access!
		if ($this->isKitchenStaff()) { return TRUE; }
			// if no user is logged in access is denied at all
		if(!$this->securityContext->getToken()->getUser()->getProfile() instanceof Profile) { return FALSE; }

			// check access in terms of given accesstype...
		switch ($accesstype) {
			case (self::AT_MEAL_PARTICIPATION):
				/**
				 * Parameters:
				 * @var \Mealz\MealBundle\Entity\Meal 	meal
				 */
				if (!isset($params['meal']) || !$params['meal'] instanceof \Mealz\MealBundle\Entity\Meal) return FALSE;
				return $this->isToggleParticipationAllowed($params['meal']->getDay()->getLockParticipationDateTime());
				break;
			default:
					// by default refuse access
				return FALSE;
		}
	}
}