<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\MealOfferedEvent;
use App\Mealz\MealBundle\Service\OfferService;
use App\Mealz\MealBundle\Service\Publisher\Publisher;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MealOfferSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private PublisherInterface $publisher;

    public function __construct(LoggerInterface $logger, PublisherInterface $publisher)
    {
        $this->logger = $logger;
        $this->publisher = $publisher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MealOfferedEvent::class => 'onOfferUpdate',
            MealOfferedEvent::class => 'onMealOffered',
            MealOfferAcceptedEvent::class => 'onMealAccepted',
            MealOfferCanceledEvent::class => 'onMealAccepted',
        ];
    }

    public function onOfferUpdate(MealOfferedEvent $event): void
    {
        $ok = $this->publisher->publish(
            Publisher::TOPIC_UPDATE_OFFER,
            [
                'mealId' => $event->getParticipant()->getMeal()->getId(),
                'isAvailable' => !empty(OfferService::getOffers($event->getParticipant()->getMeal())),
                'date' => $event->getParticipant()->getMeal()->getDateTime()->format('Y-m-d'),
                'dishSlug' => $event->getParticipant()->getMeal()->getDish()->getSlug()
            ]
        );

        if (!$ok) {
            $this->logger->error('publish failure', ['topic' => Publisher::TOPIC_UPDATE_OFFER]);
        }
    }
}
