<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OfferUpdatePublisher implements EventSubscriberInterface
{
    private PublisherInterface $publisher;
    private LoggerInterface $logger;
    private ParticipationService $participationService;

    public function __construct(
        PublisherInterface $publisher,
        LoggerInterface $logger,
        ParticipationService $participationService)
    {
        $this->publisher            = $publisher;
        $this->logger               = $logger;
        $this->participationService = $participationService;
    }


    public static function getSubscribedEvents() : array
    {
        return [
            ParticipationUpdateEvent::class => 'onOfferUpdate',
        ];
    }

    public function onOfferUpdate(ParticipationUpdateEvent $event): void
    {
        $success = $this->publisher->publish('/offer-update', 
            [
                'mealId'          => $event->getParticipant()->getMeal()->getId(),
                'isAvailable'     => $this->participationService->isOpenMeal($event->getParticipant()->getMeal())
            ]);
        if(!$success) {
            $this->logger->error('publisher: publishing to topic /offer-update failed');
        }
    }
}