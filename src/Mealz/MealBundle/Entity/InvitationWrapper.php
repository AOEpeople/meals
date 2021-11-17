<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Validator\Constraints as Assert;

class InvitationWrapper
{
    /**
     * @Assert\Valid
     *
     * @var Day
     */
    private $day;

    private ?Slot $slot = null;

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

    public function getSlot(): ?Slot
    {
        return $this->slot;
    }

    public function setSlot(Slot $slot): void
    {
        $this->slot = $slot;
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
