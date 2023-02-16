<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\KeepAliveConnectionEvent;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class KeepAliveConnectionSubscriber implements EventSubscriberInterface
{
    private const PUBLISH_TOPIC = 'keep-alive-connection';
    private const PUBLISH_MSG_TYPE = 'keepAliveConnection';

    private PublisherInterface $publisher;

    public function __construct(
        PublisherInterface $publisher
    ) {
        $this->publisher = $publisher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KeepAliveConnectionEvent::class => 'onKeepAliveConnection',
        ];
    }

    public function onKeepAliveConnection(KeepAliveConnectionEvent $event): void
    {
        $this->publisher->publish(
            self::PUBLISH_TOPIC,
            [],
            self::PUBLISH_MSG_TYPE
        );
    }
}
