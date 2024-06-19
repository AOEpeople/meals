<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Slot;
use Symfony\Contracts\EventDispatcher\Event;

class SlotAllocationUpdateEvent extends Event
{
    private Day $day;

    /**
     * Currently allocated/freed slot.
     */
    private ?Slot $slot;

    /**
     * Previously allocated slot.
     */
    private ?Slot $prevSlot;

    public function __construct(Day $day, ?Slot $slot, ?Slot $prev = null)
    {
        $this->day = $day;
        $this->slot = $slot;
        $this->prevSlot = $prev;
    }

    public function getDay(): Day
    {
        return $this->day;
    }

    public function getSlot(): ?Slot
    {
        return $this->slot;
    }

    public function getPreviousSlot(): ?Slot
    {
        return $this->prevSlot;
    }
}
