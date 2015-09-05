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

	public function __construct(SecurityContext $securityContext, \DateTime $now = NULL) {
		$this->securityContext = $securityContext;
		$this->now = $now ?: new \DateTime();
	}

	public function isUserAllowedToJoin(Meal $meal) {
		if ($this->isKitchenStaff()) {
			return TRUE;
		}
		if(!$this->securityContext->getToken()->getUser()->getProfile() instanceof Profile) {
			return FALSE;
		}
		if($meal->getDateTime()->getTimestamp() - 9000 > $this->now->getTimestamp()) {
			// if: meal is in two and a half hours or earlier
			return TRUE;
		}
		return FALSE;
	}

	public function isUserAllowedToLeave(Meal $meal) {
		if($this->isKitchenStaff()) {
			return TRUE;
		}
		if(!$this->securityContext->getToken()->getUser()->getProfile() instanceof Profile) {
			return FALSE;
		}
		if($meal->getDateTime()->getTimestamp() - 9000 > $this->now->getTimestamp()) {
			// if: meal is in two and a half hours or earlier
			return TRUE;
		}
		return FALSE;
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

}