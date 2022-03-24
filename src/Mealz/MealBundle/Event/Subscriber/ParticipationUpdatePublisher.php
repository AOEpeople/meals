<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\MealBundle\Service\Publisher\Publisher;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use App\Mealz\UserBundle\Entity\Profile;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ParticipationUpdatePublisher implements EventSubscriberInterface
{
    private PublisherInterface $publisher;
    private LoggerInterface $logger;
    private ParticipationService $participationService;

    public function __construct(PublisherInterface $publisher, LoggerInterface $logger, ParticipationService $participationService)
    {
        $this->publisher = $publisher;
        $this->logger = $logger;
        $this->participationService = $participationService;
    }

    public static function getSubscribedEvents(): array
    {
        return [];
//        return [
//            ParticipationUpdateEvent::class => 'onParticipationUpdate',
//        ];
    }

    public function onParticipationUpdate(ParticipationUpdateEvent $event): void
    {
//        $count = $event->getParticipant()->getMeal()->getParticipants()->count();
//
//        if ($event->getParticipant()->getProfile() instanceof Profile && $event->getParticipant()->getProfile()->isGuest()) {
//            ++$count;
//        }
//
//        $success = $this->publisher->publish(Publisher::TOPIC_PARTICIPATION_UPDATES,
//            [
//                'mealId' => $event->getParticipant()->getMeal()->getId(),
//                'count' => $count,
//                'isAvailable' => $this->participationService->isAvailable($event->getParticipant()->getMeal()),
//            ]);
//        if (!$success) {
//            $this->logger->error('topic publish error', ['topic' => Publisher::TOPIC_PARTICIPATION_UPDATES]);
//        }
    }
}
