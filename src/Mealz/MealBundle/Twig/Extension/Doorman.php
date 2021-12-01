<?php

namespace App\Mealz\MealBundle\Twig\Extension;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Service\Doorman as DoormanService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class Doorman.
 */
class Doorman extends AbstractExtension
{
    /**
     * @var DoormanService
     */
    protected $doormanService;

    /**
     * Doorman constructor.
     */
    public function __construct(DoormanService $doormanService)
    {
        $this->doormanService = $doormanService;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('is_allowed_to_join', [$this, 'isUserAllowedToJoin']),
            new TwigFunction('is_allowed_to_leave', [$this, 'isUserAllowedToLeave']),
            new TwigFunction('is_allowed_to_swap', [$this, 'isUserAllowedToSwap']),
            new TwigFunction('is_allowed_to_unswap', [$this, 'isUserAllowedToUnswap']),
            new TwigFunction('is_allowed_to_add_guest', [$this, 'isUserAllowedToAddGuest']),
            new TwigFunction('is_allowed_to_remove_guest', [$this, 'isUserAllowedToRemoveGuest']),
            new TwigFunction('is_participation_pending', [$this, 'isParticipationPending']),
            new TwigFunction('is_offer_available', [$this, 'isOfferAvailable']),
        ];
    }

    /**
     * @return bool
     */
    public function isUserAllowedToJoin(Meal $meal)
    {
        // proxy method
        return $this->doormanService->isUserAllowedToJoin($meal);
    }

    /**
     * @return bool
     */
    public function isUserAllowedToLeave(Meal $meal)
    {
        // proxy method
        return $this->doormanService->isUserAllowedToLeave($meal);
    }

    /**
     * @return bool
     */
    public function isUserAllowedToSwap(Meal $meal)
    {
        //proxy method
        return $this->doormanService->isUserAllowedToSwap($meal);
    }

    /**
     * @return bool
     */
    public function isUserAllowedToAddGuest(Meal $meal)
    {
        // proxy method
        return $this->doormanService->isUserAllowedToAddGuest($meal);
    }

    /**
     * @return bool
     */
    public function isUserAllowedToRemoveGuest(Meal $meal)
    {
        // proxy method
        return $this->doormanService->isUserAllowedToRemoveGuest($meal);
    }

    /**
     * @return bool
     */
    public function isUserAllowedToUnswap(Meal $meal, Participant $participant)
    {
        //proxy method
        return $this->doormanService->isUserAllowedToUnswap($meal, $participant);
    }

    /**
     * @return bool
     */
    public function isParticipationPending(Participant $participant)
    {
        //proxy method
        return $this->doormanService->isParticipationPending($participant);
    }

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    public function isOfferAvailable(Meal $meal, Participant $participant = null)
    {
        //proxy method
        return $this->doormanService->isOfferAvailable($meal, $participant);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'doorman';
    }
}
