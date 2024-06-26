<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use App\Mealz\MealBundle\Service\SlotService;
use DateTime;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SlotAllocationSubscriber implements EventSubscriberInterface
{
    private const string PUBLISH_TOPIC = 'slot-allocation-updates';
    private const string PUBLISH_MSG_TYPE = 'slotAllocationUpdate';

    public function __construct(
        private readonly PublisherInterface $publisher,
        private readonly SlotService $slotSrv
    ) {
    }

    /**
     * @return string[]
     *
     * @psalm-return array{SlotAllocationUpdateEvent::class: 'onSlotAllocationUpdate'}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SlotAllocationUpdateEvent::class => 'onSlotAllocationUpdate',
        ];
    }

    public function onSlotAllocationUpdate(SlotAllocationUpdateEvent $event): void
    {
        // do not publish if both slots are unrestricted (limit = 0)
        if ($this->eventInvolvesRestrictedSlot($event)) {
            $day = $event->getDay();
            $newSlot = $event->getSlot();
            $prevSlot = $event->getPreviousSlot();
            $this->publisher->publish(
                self::PUBLISH_TOPIC,
                [
                    'weekId' => $day->getWeek()->getId(),
                    'dayId' => $day->getId(),
                    'newSlot' => $this->addSlot($newSlot, $day->getDateTime()),
                    'prevSlot' => $this->addSlot($prevSlot, $day->getDateTime()),
                ],
                self::PUBLISH_MSG_TYPE
            );
        }
    }

    /**
     * @return (int|null)[]
     *
     * @psalm-return array{slotId: int|null, limit: int, count: int}
     */
    private function addSlot(?Slot $slot, DateTime $dateTime): array
    {
        if (null !== $slot) {
            $count = $this->slotSrv->getSlotsStatusOn($dateTime)[$slot->getSlug()];

            return [
                'slotId' => $slot->getId(),
                'limit' => $slot->getLimit(),
                'count' => $count,
            ];
        }

        return [
            'slotId' => 0,
            'limit' => 0,
            'count' => 0,
        ];
    }

    private function eventInvolvesRestrictedSlot(SlotAllocationUpdateEvent $event): bool
    {
        $prevSlot = $event->getPreviousSlot();
        $newSlot = $event->getSlot();

        return $newSlot && $event->getSlot()->getLimit() > 0 || $prevSlot && $prevSlot->getLimit() > 0;
    }
}
