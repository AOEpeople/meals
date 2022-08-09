<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use App\Mealz\MealBundle\Service\SlotService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SlotAllocationSubscriber implements EventSubscriberInterface
{
    private const PUBLISH_TOPIC = 'slot-allocation-updates';
    private const PUBLISH_MSG_TYPE = 'slotAllocationUpdate';

    private PublisherInterface $publisher;
    private SlotService $slotSrv;

    public function __construct(
        PublisherInterface $publisher,
        SlotService $slotSrv
    ) {
        $this->publisher = $publisher;
        $this->slotSrv = $slotSrv;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SlotAllocationUpdateEvent::class => 'onSlotAllocationUpdate',
        ];
    }

    public function onSlotAllocationUpdate(SlotAllocationUpdateEvent $event): void
    {
        // do not publish if update involves unrestricted slot (limit: 0)
        if (!$this->eventInvolvesRestrictedSlot($event)) {
            return;
        }

        $day = $event->getDay();
        $newSlot = $event->getSlot();
        $prevSlot = $event->getPreviousSlot();
        $this->publisher->publish(
            self::PUBLISH_TOPIC,
            [
                'weekId' => $day->getWeek()->getId(),
                'dayId' => $day->getId(),
                'newSlot' => $this->addSlot($newSlot),
                'prevSlot' => $this->addSlot($prevSlot),
            ],
            self::PUBLISH_MSG_TYPE
        );
    }

    private function addSlot(?Slot $slot): array
    {
        if($slot !== null) {
            return [
                'slotId' => $slot->getId(),
                'limit' => $slot->getLimit(),
                'count' => $slot->getParticipants()->count(),
            ];
        }
        return [];
    }

    private function eventInvolvesRestrictedSlot(SlotAllocationUpdateEvent $event): bool
    {
        if (0 < $event->getSlot()->getLimit()) {
            return true;
        }

        $prevSlot = $event->getPreviousSlot();

        return $prevSlot && $prevSlot->getLimit() > 0;
    }
}
