<?php


namespace Mealz\MealBundle\Twig\Extension;

use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Service\Doorman as DoormanService;

class Doorman extends \Twig_Extension {

	/**
	 * @var DoormanService
	 */
	protected $doormanService;

	public function __construct(DoormanService $doormanService) {
		$this->doormanService = $doormanService;
	}

	public function getFunctions() {
		return array(
			'is_allowed_to_join' => new \Twig_Function_Method($this, 'isUserAllowedToJoin'),
			'is_allowed_to_leave' => new \Twig_Function_Method($this, 'isUserAllowedToLeave'),
            'is_allowed_to_swap' => new \Twig_Function_Method($this, 'isUserAllowedToSwap'),
			'is_allowed_to_add_guest' => new \Twig_Function_Method($this, 'isUserAllowedToAddGuest'),
			'is_allowed_to_remove_guest' => new \Twig_Function_Method($this, 'isUserAllowedToRemoveGuest'),
            'is_allowed_to_unswap' => new \Twig_Function_Method($this, 'isUserAllowedToUnswap'),
            'is_participation_pending' => new \Twig_Function_Method($this, 'isParticipationPending'),
            'is_offer_available' => new \Twig_Function_Method($this, 'isOfferAvailable'),
		);
	}

	public function isUserAllowedToJoin(Meal $meal) {
		// proxy method
		return $this->doormanService->isUserAllowedToJoin($meal);
	}

	public function isUserAllowedToLeave(Meal $meal) {
		// proxy method
		return $this->doormanService->isUserAllowedToLeave($meal);
	}

	public function isUserAllowedToSwap (Meal $meal) {
	    //proxy method
        return $this->doormanService->isUserAllowedToSwap($meal);
    }

	public function isUserAllowedToAddGuest(Meal $meal) {
		// proxy method
		return $this->doormanService->isUserAllowedToAddGuest($meal);
	}

	public function isUserAllowedToRemoveGuest(Meal $meal) {
		// proxy method
		return $this->doormanService->isUserAllowedToRemoveGuest($meal);
	}

	public function isUserAllowedToUnswap (Meal $meal, Participant $participant) {
	    //proxy method
        return $this->doormanService->isUserAllowedToUnswap($meal, $participant);
    }

    public function isParticipationPending (Participant $participant) {
	    //proxy method
        return $this->doormanService->isParticipationPending($participant);
    }

    public function isOfferAvailable (Meal $meal, Participant $user = null) {
	    //proxy method
        return $this->doormanService->isOfferAvailable($meal, $user);
    }

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName() {
		return 'doorman';
	}
}