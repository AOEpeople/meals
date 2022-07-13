<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

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
        $this->publisher->publish(
            self::PUBLISH_TOPIC,
            [
                'date' => $day->format('Ymd'),
                'slotAllocation' => $this->slotSrv->getSlotsStatusOn($day),
            ],
            self::PUBLISH_MSG_TYPE
        );
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
