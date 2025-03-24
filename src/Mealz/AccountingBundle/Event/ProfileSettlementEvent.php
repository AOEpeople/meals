<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Event;

use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Contracts\EventDispatcher\Event;

final class ProfileSettlementEvent extends Event
{
    public const NAME = 'meals.account.settled';

    protected Profile $profile;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }
}
