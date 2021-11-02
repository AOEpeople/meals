<?php

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Security\Core\Security;

/**
 * central business logic to determine if the currently logged in user is allowed to do a certain action.
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
     * Doorman constants defining access types.
     *
     * @see $this->hasAccessTo
     */
    private const AT_MEAL_PARTICIPATION = 0;

    /**
     * Current timestamp.
     */
    protected int $now;

    protected Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
        $this->now = time();
    }

    public function isUserAllowedToJoin(Meal $meal): bool
    {
        if (false === $this->security->getUser()->getProfile() instanceof Profile || true === $meal->isParticipationLimitReached()) {
            return false;
        }

        if (true === $this->hasAccessTo(self::AT_MEAL_PARTICIPATION, ['meal' => $meal])) {
            return true;
        }

        return $this->isToggleParticipationAllowed($meal->getDateTime())
                && $this->hasAccessTo(self::AT_MEAL_PARTICIPATION, ['meal' => $meal]);
    }

    /**
     * @return bool
     */
    public function isOfferAvailable(Meal $meal)
    {
        if (false === $this->security->getUser()->getProfile() instanceof Profile) {
            return false;
        }

        $participants = $meal->getParticipants();
        foreach ($participants as $participant) {
            if (true === $participant->isPending()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isUserAllowedToLeave(Meal $meal)
    {
        return $this->hasAccessTo(self::AT_MEAL_PARTICIPATION, ['meal' => $meal]);
    }

    /**
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

    public function isUserAllowedToUnswap(Meal $meal, Participant $participant): bool
    {
        return $this->isUserAllowedToSwap($meal) && $this->isParticipationPending($participant);
    }

    public function isParticipationPending(Participant $participant): bool
    {
        return 0 !== $participant->getOfferedAt();
    }

    /**
     * @return bool
     */
    public function isKitchenStaff()
    {
        return $this->security->isGranted('ROLE_KITCHEN_STAFF');
    }

    /**
     * @return bool
     */
    public function isUserAllowedToAddGuest(Meal $meal)
    {
        // @TODO: add a separate role for that
        return $this->isKitchenStaff() || $this->isUserAllowedToJoin($meal);
    }

    /**
     * @return bool
     */
    public function isUserAllowedToRemoveGuest(Meal $meal)
    {
        // @TODO: add a separate role for that
        return $this->isKitchenStaff() || $this->isUserAllowedToLeave($meal);
    }

    /**
     * @return bool
     */
    public function isUserAllowedToRequestCostAbsorption(Meal $meal)
    {
        // @TODO: add a separate role for that
        return $this->isKitchenStaff() || $this->isUserAllowedToAddGuest($meal);
    }

    /**
     * @return bool
     */
    public function isToggleParticipationAllowed(\DateTime $lockPartDateTime)
    {
        // is it still allowed to participate in the meal by now?
        return $lockPartDateTime->getTimestamp() > $this->now;
    }

    /**
     * Checking access to a vary of processes inside of meals.
     * Accesstype is a constant of class Doorman. Use this to tell the method what to check ;-)
     * To be used in future to add more acces checks.
     *
     * @param int   $accesstype What access shall be checked
     * @param array $params
     *
     * @return bool
     */
    private function hasAccessTo($accesstype, $params = [])
    {
        // check access in terms of given accesstype...
        switch ($accesstype) {
            case self::AT_MEAL_PARTICIPATION:
                if (!isset($params['meal']) || !$params['meal'] instanceof Meal) {
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
