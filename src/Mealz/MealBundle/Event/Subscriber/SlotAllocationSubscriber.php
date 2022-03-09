<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Service\Publisher\Publisher;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SlotAllocationSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private PublisherInterface $publisher;
    private ParticipantRepository $participantRepository;

    public function __construct(
        LoggerInterface $logger,
        PublisherInterface $publisher,
        ParticipantRepository $participantRepository
    ) {
        $this->logger = $logger;
        $this->publisher = $publisher;
        $this->participantRepository = $participantRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SlotAllocationUpdateEvent::class => 'onSlotAllocationUpdate',
        ];
    }

    public function onSlotAllocationUpdate(SlotAllocationUpdateEvent $event): void
    {
        $slot = $event->getSlot();
        $day = $event->getDay();

        $ok = $this->publisher->publish(
            Publisher::TOPIC_UPDATE_SLOT,
            [
                'slot' => $slot->getSlug(),
                'date' => $day->format('Ymd'),
                'count' => $this->participantRepository->getCountBySlot($slot, $day)
            ]
        );

        if (!$ok) {
            $this->logger->error('publish error', ['topic' => Publisher::TOPIC_UPDATE_SLOT]);
        }
    }
}
