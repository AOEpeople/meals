<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use Psr\Log\LoggerInterface;
use App\Mealz\MealBundle\Service\Publisher\Publisher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ParticipationUpdatePublisher implements EventSubscriberInterface
{
    private PublisherInterface $publisher;
    private LoggerInterface $logger;

    public function __construct(PublisherInterface $publisher, LoggerInterface $logger)
    {
        $this->publisher    = $publisher;
        $this->logger       = $logger;
    }

    public static function getSubscribedEvents() : array
    {
        return [
            ParticipationUpdateEvent::class => 'onParticipationUpdate',
        ];
    }

    public function onParticipationUpdate(ParticipationUpdateEvent $event): void
    {
        $count = $event->getParticipant()->getMeal()->getParticipants()->count();

        if($event->getParticipant()->getProfile() && $event->getParticipant()->getProfile()->isGuest()) {
            $count++;
        }

        $success = $this->publisher->publish(Publisher::TOPIC_PARTICIPANT_COUNT,
            [
                'mealId'            => $event->getParticipant()->getMeal()->getId(),
                'count'             => $count,
                'isLimitReached'    => $event->getParticipant()->getMeal()->isLimitReached()
            ]);
        if(!$success) {
            $this->logger->error('topic publish error', ['topic' => Publisher::TOPIC_PARTICIPANT_COUNT]);
        }
    }
}