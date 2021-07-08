<?php

namespace Mealz\UserBundle\User;

use Mealz\UserBundle\Entity\Profile;

/**
 * logged in users in this application should be able to have a profile
 */
interface UserInterface
{

    /**
     * @return Profile|null
     */
    public function getProfile();


    /**
     * @param Profile $profile
     */
    public function setProfile(Profile $profile);
}
