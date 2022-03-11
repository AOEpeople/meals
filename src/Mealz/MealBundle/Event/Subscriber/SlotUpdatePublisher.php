<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\MealBundle\Event\SlotUpdateEvent;
use App\Mealz\MealBundle\Service\Publisher\Publisher;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SlotUpdatePublisher implements EventSubscriberInterface
{
    private PublisherInterface $publisher;
    private ParticipantRepository $participantRepo;
    private LoggerInterface $logger;

    public function __construct(PublisherInterface $publisher, ParticipantRepository $participantRepo, LoggerInterface $logger)
    {
        $this->publisher = $publisher;
        $this->participantRepo = $participantRepo;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SlotUpdateEvent::class => 'onSlotUpdate',
        ];
    }

    public function onSlotUpdate(SlotUpdateEvent $event): void
    {
        $success = $this->publisher->publish(Publisher::TOPIC_UPDATE_SLOT,
            [
                'date' => $event->getParticipant()->getMeal()->getDateTime()->format('Ymd'),
                'slotSlug' => $event->getParticipant()->getSlot()->getSlug(),
                'slotCount' => $this->participantRepo->getCountBySlot(
                    $event->getParticipant()->getSlot(),
                    $event->getParticipant()->getMeal()->getDateTime()
                ),
            ]);
        if (!$success) {
            $this->logger->error('topic publish error', ['topic' => Publisher::TOPIC_UPDATE_SLOT]);
        }
    }
}
