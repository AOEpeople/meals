<?php

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * central business logic to determine if the currently logged-in user is allowed to do a certain action.
 *
 * For instance a user should not be allowed to register for a meal, that
 * starts in 10 minutes, but maybe a user with a special role ("cook") should be able
 * to do that even after the meal has started. The time for registration could also be
 * limited by the meal itself (something special has to be ordered a day earlier) or the number
 * of available meals could be limited.
 *
 * This logic should be accessible in controllers, templates and services.
 */
#[Autoconfigure(lazy: true)]
class Doorman
{
    /**
     * Doorman constants defining access types.
     *
     * @see $this->hasAccessTo
     */
    private const int AT_MEAL_PARTICIPATION = 0;

    /**
     * Current timestamp.
     */
    protected int $now;

    protected Security $security;
    private MealAvailabilityService $availabilityService;

    public function __construct(Security $security, MealAvailabilityService $availabilityService)
    {
        $this->security = $security;
        $this->availabilityService = $availabilityService;
        $this->now = time();
    }

    public function isUserAllowedToJoin(Meal $meal, array $dishSlugs = []): bool
    {
        $mealAvailability = $this->availabilityService->getByMeal($meal);

        if (true === is_bool($mealAvailability)) {
            $mealIsAvailable = $mealAvailability;
        } else {
            $mealIsAvailable =
                (true === $mealAvailability['available'])
                && ((1 > count($dishSlugs)) || (0 === count(array_diff($mealAvailability['availableWith'], $dishSlugs))));
        }

        if (false === $this->security->getUser()->getProfile() instanceof Profile || false === $mealIsAvailable) {
            return false;
        }

        if (true === $this->hasAccessTo(self::AT_MEAL_PARTICIPATION, ['meal' => $meal])) {
            return true;
        }

        return $this->isToggleParticipationAllowed($meal->getDateTime())
                && $this->hasAccessTo(self::AT_MEAL_PARTICIPATION, ['meal' => $meal]);
    }

    public function isUserAllowedToJoinEvent(EventParticipation $eventParticipation): bool
    {
        if (false === $this->security->getUser()->getProfile() instanceof Profile) {
            return false;
        }

        return $this->isToggleParticipationAllowed($eventParticipation->getDay()->getDateTime()->setTime(16, 0));
    }

    public function isOfferAvailable(Meal $meal): bool
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

    public function isUserAllowedToLeave(Meal $meal): bool
    {
        return $this->hasAccessTo(self::AT_MEAL_PARTICIPATION, ['meal' => $meal]);
    }

    public function isUserAllowedToSwap(Meal $meal): bool
    {
        if ($meal->getDay()->getLockParticipationDateTime()->getTimestamp() < $this->now && $this->now < $meal->getDateTime()->getTimestamp()) {
            return true;
        }

        return false;
    }

    public function isUserAllowedToUnswap(Meal $meal, Participant $participant): bool
    {
        return $this->isUserAllowedToSwap($meal) && $this->isParticipationPending($participant);
    }

    public function isParticipationPending(Participant $participant): bool
    {
        return 0 !== $participant->getOfferedAt();
    }

    public function isKitchenStaff(): bool
    {
        return $this->security->isGranted('ROLE_KITCHEN_STAFF');
    }

    public function isToggleParticipationAllowed(DateTime $lockPartDateTime): bool
    {
        // is it still allowed to participate in the meal by now?
        return $lockPartDateTime->getTimestamp() > $this->now;
    }

    /**
     * Checking access to a vary of processes inside of meals.
     * $accesstype is a constant of class Doorman. Use this to tell the method what to check ;-)
     * To be used in future to add more access checks.
     *
     * @param int   $accesstype What access shall be checked
     * @param array $params
     */
    private function hasAccessTo($accesstype, $params = []): bool
    {
        // check access in terms of given $accesstype...
        switch ($accesstype) {
            case self::AT_MEAL_PARTICIPATION:
                if (false === isset($params['meal']) || false === ($params['meal'] instanceof Meal)) {
                    return false;
                }

                return $this->isToggleParticipationAllowed($params['meal']->getDay()->getLockParticipationDateTime());
            default:
                // by default refuse access
                return false;
        }
    }
}
