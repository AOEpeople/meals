<?php


namespace Mealz\MealBundle\Twig\Extension;

use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Service\Doorman as DoormanService;
use Twig\TwigFunction;
use Twig_Extension;

/**
 * Class Doorman
 * @package Mealz\MealBundle\Twig\Extension
 */
class Doorman extends Twig_Extension
{

    /**
     * @var DoormanService
     */
    protected $doormanService;

    /**
     * Doorman constructor.
     * @param DoormanService $doormanService
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
        return array(
            new TwigFunction('is_allowed_to_join', [$this, 'isUserAllowedToJoin']),
            new TwigFunction('is_allowed_to_leave', [$this, 'isUserAllowedToLeave']),
            new TwigFunction('is_allowed_to_swap', [$this, 'isUserAllowedToSwap']),
            new TwigFunction('is_allowed_to_unswap', [$this, 'isUserAllowedToUnswap']),
            new TwigFunction('is_allowed_to_add_guest', [$this, 'isUserAllowedToAddGuest']),
            new TwigFunction('is_allowed_to_remove_guest', [$this, 'isUserAllowedToRemoveGuest']),
            new TwigFunction('is_participation_pending', [$this, 'isParticipationPending']),
            new TwigFunction('is_offer_available', [$this, 'isOfferAvailable']),
        );
    }

    /**
     * @param Meal $meal
     * @return bool
     */
    public function isUserAllowedToJoin(Meal $meal)
    {
        // proxy method
        return $this->doormanService->isUserAllowedToJoin($meal);
    }

    /**
     * @param Meal $meal
     * @return bool
     */
    public function isUserAllowedToLeave(Meal $meal)
    {
        // proxy method
        return $this->doormanService->isUserAllowedToLeave($meal);
    }

    /**
     * @param Meal $meal
     * @return bool
     */
    public function isUserAllowedToSwap(Meal $meal)
    {
        //proxy method
        return $this->doormanService->isUserAllowedToSwap($meal);
    }

    /**
     * @param Meal $meal
     * @return bool
     */
    public function isUserAllowedToAddGuest(Meal $meal)
    {
        // proxy method
        return $this->doormanService->isUserAllowedToAddGuest($meal);
    }

    /**
     * @param Meal $meal
     * @return bool
     */
    public function isUserAllowedToRemoveGuest(Meal $meal)
    {
        // proxy method
        return $this->doormanService->isUserAllowedToRemoveGuest($meal);
    }

    /**
     * @param Meal $meal
     * @param Participant $participant
     * @return bool
     */
    public function isUserAllowedToUnswap(Meal $meal, Participant $participant)
    {
        //proxy method
        return $this->doormanService->isUserAllowedToUnswap($meal, $participant);
    }

    /**
     * @param Participant $participant
     * @return bool
     */
    public function isParticipationPending(Participant $participant)
    {
        //proxy method
        return $this->doormanService->isParticipationPending($participant);
    }

    /**
     * @param Meal $meal
     * @param Participant $participant
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
