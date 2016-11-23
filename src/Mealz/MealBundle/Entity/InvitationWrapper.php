<?php

namespace Mealz\MealBundle\Entity;

use Mealz\MealBundle\Entity\Day;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class InvitationWrapper
 * @package Mealz\MealBundle\Form\Guest
 */
class InvitationWrapper
{

    /**
     * @Assert\Valid
     * @var Day
     */
    private $day;

    /**
     * @Assert\Valid
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
    public function setDay($day)
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
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }



}