<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\MealBundle\Service\Publisher\Publisher;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SlotAllocationSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private PublisherInterface $publisher;
    private ParticipationService $participationSrv;

    public function __construct(
        LoggerInterface $logger,
        PublisherInterface $publisher,
        ParticipationService $participationSrv
    ) {
        $this->logger = $logger;
        $this->publisher = $publisher;
        $this->participationSrv = $participationSrv;
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
        $this->publish([
            'date' => $day->format('Ymd'),
            'slotAllocation' => $this->participationSrv->getSlotsStatusOn($day)
        ]);
    }

    private function eventInvolvesRestrictedSlot(SlotAllocationUpdateEvent $event): bool
    {
        if (0 < $event->getSlot()->getLimit()) {
            return true;
        }

        $prevSlot = $event->getPreviousSlot();

        return $prevSlot && $prevSlot->getLimit() > 0;
    }

    private function publish(array $data): void
    {
        $published = $this->publisher->publish(
            Publisher::TOPIC_SLOT_ALLOCATION_UPDATES,
            $data
        );

        if (!$published) {
            $this->logger->error('publish failure', ['topic' => Publisher::TOPIC_SLOT_ALLOCATION_UPDATES]);
        }
    }
}
