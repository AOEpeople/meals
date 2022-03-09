<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event;

use App\Mealz\MealBundle\Entity\Slot;
use DateTime;
use Symfony\Contracts\EventDispatcher\Event;

class SlotAllocationUpdateEvent extends Event
{
    private DateTime $day;
    private Slot $slot;

    public function __construct(Slot $slot, DateTime $day)
    {
        $this->slot = $slot;
        $this->day = $day;
    }

    public function getDay(): DateTime
    {
        return $this->day;
    }

    public function getSlot(): Slot
    {
        return $this->slot;
    }
}
