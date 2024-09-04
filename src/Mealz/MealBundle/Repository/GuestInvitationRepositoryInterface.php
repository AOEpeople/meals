<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Entity\GuestInvitation;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template-extends ObjectRepository<GuestInvitation>
 */
interface GuestInvitationRepositoryInterface extends ObjectRepository
{
    /**
     * Gets the guest invitation from a particular user on a particular day.
     */
    public function findOrCreateInvitation(Profile $host, Day $day, EventParticipation $eventParticipation): GuestInvitation;
}
