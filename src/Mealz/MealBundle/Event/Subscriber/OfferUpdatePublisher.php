<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\OfferUpdateEvent;
use App\Mealz\MealBundle\Service\OfferService;
use App\Mealz\MealBundle\Service\Publisher\Publisher;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OfferUpdatePublisher implements EventSubscriberInterface
{
    private PublisherInterface $publisher;
    private LoggerInterface $logger;

    public function __construct(
        PublisherInterface $publisher,
        LoggerInterface $logger)
    {
        $this->publisher            = $publisher;
        $this->logger               = $logger;
    }

    public static function getSubscribedEvents() : array
    {
        return [
            OfferUpdateEvent::class => 'onOfferUpdate',
        ];
    }

    public function onOfferUpdate(OfferUpdateEvent $event): void
    {
        $success = $this->publisher->publish(Publisher::TOPIC_UPDATE_OFFER,
            [
                'mealId'          => $event->getParticipant()->getMeal()->getId(),
                'isAvailable'     => !empty(OfferService::getOffers($event->getParticipant()->getMeal())),
                'date'            => $event->getParticipant()->getMeal()->getDateTime()->format('Y-m-d'),
                'dishSlug'        => $event->getParticipant()->getMeal()->getDish()->getSlug()
            ]);
        if(!$success) {
            $this->logger->error('topic publish error', ['topic' => Publisher::TOPIC_UPDATE_OFFER]);
        }
    }
}