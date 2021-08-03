<?php

namespace Mealz\MealBundle\Service;

use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Security\Core\Security;

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
class Doorman
{

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
     * @var Security
     */
    protected $security;

    /**
     * Doorman constructor.
     * @param Security $security
     * @param string   $lockParticipationAt
     */
    public function __construct(Security $security, $lockParticipationAt = '-1 day 12:00')
    {
        $this->security = $security;
        $this->now = time();
        $this->lockParticipationAt = $lockParticipationAt;
    }

    /**
     * @param Meal $meal
     * @return bool
     */
    public function isUserAllowedToJoin(Meal $meal)
    {
        if ($this->security->getUser()->getProfile() instanceof Profile === false || $meal->isParticipationLimitReached() === true) {
            return false;
        }
        if ($this->hasAccessTo(self::AT_MEAL_PARTICIPATION, ['meal' => $meal]) === true) {
            return true;
        }
        return ($this->isToggleParticipationAllowed($meal->getDateTime()) && $this->hasAccessTo(self::AT_MEAL_PARTICIPATION, ['meal' => $meal]));
    }

    /**
     * @param Meal $meal
     * @return bool
     */
    public function isOfferAvailable(Meal $meal)
    {
        if ($this->security->getUser()->getProfile() instanceof Profile === false) {
            return false;
        }

        $participants = $meal->getParticipants();
        foreach ($participants as $participant) {
            if ($participant->isPending() === true) {
                return true;
            }
        }
    }

    /**
     * @param Meal $meal
     * @return bool
     */
    public function isUserAllowedToLeave(Meal $meal)
    {
        return $this->hasAccessTo(self::AT_MEAL_PARTICIPATION, ['meal' => $meal]);
    }

    /**
     * @param Meal $meal
     * @return bool
     */
    public function isUserAllowedToSwap(Meal $meal)
    {
        if ($meal->getDay()->getLockParticipationDateTime()->getTimestamp() < $this->now && $this->now < $meal->getDateTime()->getTimestamp()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Meal $meal
     * @param Participant $participant
     * @return bool
     */
    public function isUserAllowedToUnswap(Meal $meal, Participant $participant)
    {
        return ($this->isUserAllowedToSwap($meal) && $this->isParticipationPending($participant));
    }

    /**
     * @param Participant $participant
     * @return bool
     */
    public function isParticipationPending(Participant $participant)
    {
        return $participant->getOfferedAt() !== 0;
    }

    /**
     * @return bool
     */
    public function isKitchenStaff()
    {
        return $this->security->isGranted('ROLE_KITCHEN_STAFF');
    }

    /**
     * @param Meal $meal
     * @return bool
     */
    public function isUserAllowedToAddGuest(Meal $meal)
    {
        // @TODO: add a separate role for that
        return $this->isKitchenStaff() || $this->isUserAllowedToJoin($meal);
    }

    /**
     * @param Meal $meal
     * @return bool
     */
    public function isUserAllowedToRemoveGuest(Meal $meal)
    {
        // @TODO: add a separate role for that
        return $this->isKitchenStaff() || $this->isUserAllowedToLeave($meal);
    }

    /**
     * @param Meal $meal
     * @return bool
     */
    public function isUserAllowedToRequestCostAbsorption(Meal $meal)
    {
        // @TODO: add a separate role for that
        return $this->isKitchenStaff() || $this->isUserAllowedToAddGuest($meal);
    }

    /**
     * @param \DateTime $lockPartDateTime
     * @return bool
     */
    public function isToggleParticipationAllowed(\DateTime $lockPartDateTime)
    {
        // is it still allowed to participate in the meal by now?
        return ($lockPartDateTime->getTimestamp() > $this->now);
    }

    /**
     * Checking access to a vary of processes inside of meals.
     * Accesstype is a constant of class Doorman. Use this to tell the method what to check ;-)
     * To be used in future to add more acces checks.
     *
     * @param integer $accesstype What access shall be checked
     * @param array $params
     * @return bool
     */
    private function hasAccessTo($accesstype, $params = [])
    {
        // if no user is logged in access is denied at all
        if ($this->security->getUser()->getProfile() instanceof Profile === false) {
            return false;
        }

        // check access in terms of given accesstype...
        switch ($accesstype) {
            case (self::AT_MEAL_PARTICIPATION):
                /**
                 * Parameters:
                 * @var \Mealz\MealBundle\Entity\Meal    meal
                 */
                if (!isset($params['meal']) || !$params['meal'] instanceof \Mealz\MealBundle\Entity\Meal) {
                    return false;
                }
                return $this->isToggleParticipationAllowed($params['meal']->getDay()->getLockParticipationDateTime());
                break;
            default:
                // by default refuse access
                return false;
        }
    }
}
