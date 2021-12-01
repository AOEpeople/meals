<?php

namespace App\Mealz\MealBundle\Entity;

use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class InvitationWrapper.
 */
class InvitationWrapper
{
    /**
     * @Assert\Valid
     *
     * @var Day
     */
    private $day;

    /**
     * @Assert\Valid
     *
     * @var Profile
     */
    private $profile;

    /**
     * @return Day
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param Day $day
     */
    public function setDay($day): void
    {
        $this->day = $day;
    }

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param Profile $profile
     */
    public function setProfile($profile): void
    {
        $this->profile = $profile;
    }
}
