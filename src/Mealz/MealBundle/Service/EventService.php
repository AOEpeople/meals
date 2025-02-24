<?php

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Event\MealOfferAcceptedEvent;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class EventService
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function triggerJoinEvents(Participant $participant, ?Profile $offerer): void
    {
        if (null !== $offerer) {
            $this->eventDispatcher->dispatch(new MealOfferAcceptedEvent($participant, $offerer));

            return;
        }

        $this->eventDispatcher->dispatch(new ParticipationUpdateEvent($participant));

        $slot = $participant->getSlot();
        if (null !== $slot) {
            $this->eventDispatcher->dispatch(new SlotAllocationUpdateEvent($participant->getMeal()->getDay(), $slot));
        }
    }

    public function triggerLeaveEvents(Participant $participant): void
    {
        $this->eventDispatcher->dispatch(new ParticipationUpdateEvent($participant));

        $slot = $participant->getSlot();
        if (null !== $slot) {
            $this->eventDispatcher->dispatch(
                new SlotAllocationUpdateEvent($participant->getMeal()->getDay(), $slot)
            );
        }
    }
}
