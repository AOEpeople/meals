<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\GuestInvitation;
use App\Mealz\UserBundle\Entity\Profile;

interface GuestInvitationRepositoryInterface
{
    /**
     * Gets the guest invitation from a particular user on a particular day.
     */
    public function findOrCreateInvitation(Profile $host, Day $day): GuestInvitation;

    public function find($id): ?GuestInvitation;
}
