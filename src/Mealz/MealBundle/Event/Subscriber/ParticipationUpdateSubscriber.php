<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ParticipationUpdateSubscriber implements EventSubscriberInterface
{
    private const PUBLISH_TOPIC = 'participation-updates';
    private const PUBLISH_MSG_TYPE = 'participationUpdate';

    private PublisherInterface $publisher;

    public function __construct(PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ParticipationUpdateEvent::class => 'onUpdate',
        ];
    }

    /**
     * Triggers action when a participation is updated, e.g. join, delete.
     */
    public function onUpdate(ParticipationUpdateEvent $event): void
    {
        $meal = $event->getParticipant()->getMeal();
        if (!$meal->isOpen()) { // do not send updates for past meals
            return;
        }
        $parentId = null;
        if (null !== $meal->getDish()->getParent()) {
            $parentId = $meal->getDish()->getParent()->getId();
        }

        $data = [
            'weekId' => $meal->getDay()->getWeek()->getId(),
            'dayId' => $meal->getDay()->getId(),
            'meal' => [
                'mealId' => $meal->getId(),
                'parentId' => $parentId,
                'limit' => $meal->getParticipationLimit(),
                'reachedLimit' => $meal->hasReachedParticipationLimit(),
                'isOpen' => $meal->isOpen(),
                'isLocked' => $meal->isLocked(),
                'participations' => $meal->getParticipants()->count(),
            ],
        ];

        $this->publisher->publish(self::PUBLISH_TOPIC, $data, self::PUBLISH_MSG_TYPE);
    }
}
