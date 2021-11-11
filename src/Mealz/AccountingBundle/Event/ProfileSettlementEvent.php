<?php
declare(strict_types=1);
namespace App\Mealz\AccountingBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Mealz\UserBundle\Entity\Profile;

class ProfileSettlementEvent extends Event
{
    public const NAME = 'profile.settlement.confirm';

    /**
     * @var Profile
     */
    protected Profile $profile;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
    }

    /**
     * @return Profile
     */
    public function getProfile(): Profile
    {
        return $this->profile;
    }
}