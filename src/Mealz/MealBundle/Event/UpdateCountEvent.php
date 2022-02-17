<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Contracts\EventDispatcher\Event;

class UpdateCountEvent extends Event
{
    private Meal $meal;
    private Profile $profile;

    /**
     * @param Meal $meal
     * @param Profile $profile
     */
    public function __construct(Meal $meal, ?Profile $profile=null)
    {
        $this->meal = $meal;
        $this->profile = $profile;
    }

    /**
     * @return Profile|null
     */
    public function getProfile(): ?Profile
    {
        return $this->profile;
    }


    /**
     * @return Meal
     */
    public function getMeal(): Meal
    {
        return $this->meal;
    }
}
