<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\User;

use App\Mealz\UserBundle\Entity\Profile;

/**
 * logged in users in this application should be able to have a profile.
 */
interface UserInterface
{
    public function getProfile(): ?Profile;

    public function setProfile(Profile $profile): void;
}
