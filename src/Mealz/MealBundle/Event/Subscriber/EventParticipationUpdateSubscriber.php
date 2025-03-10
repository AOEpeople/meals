<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\EventParticipationUpdateEvent;
use App\Mealz\MealBundle\Service\EventParticipationService;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EventParticipationUpdateSubscriber implements EventSubscriberInterface
{
    private const PUBLISH_TOPIC = 'event-participation-updates';
    private const PUBLISH_MSG_TYPE = 'eventParticipationUpdate';

    private PublisherInterface $publisher;
    private EventParticipationService $eventPartSrv;

    public function __construct(
        PublisherInterface $publisher,
        EventParticipationService $eventPartSrv
    ) {
        $this->publisher = $publisher;
        $this->eventPartSrv = $eventPartSrv;
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            EventParticipationUpdateEvent::class => 'onEventParticipationUpdate',
        ];
    }

    public function onEventParticipationUpdate(EventParticipationUpdateEvent $event): void
    {
        $eventParticipation = $event->getEventParticipation();

        $data = [
            'weekId' => $eventParticipation->getDay()->getWeek()->getId(),
            'dayId' => $eventParticipation->getDay()->getId(),
            'event' => [
                'eventId' => $eventParticipation->getEvent()->getId(),
                'participations' => count($eventParticipation->getParticipants()),
            ],
        ];

        $this->publisher->publish(self::PUBLISH_TOPIC, $data, self::PUBLISH_MSG_TYPE);
    }
}
