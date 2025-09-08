<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\KeepAliveConnectionEvent;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class KeepAliveConnectionSubscriber implements EventSubscriberInterface
{
    private const string PUBLISH_TOPIC = 'keep-alive-connection';
    private const string PUBLISH_MSG_TYPE = 'keepAliveConnection';

    public function __construct(
        private readonly PublisherInterface $publisher
    ) {
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KeepAliveConnectionEvent::class => 'onKeepAliveConnection',
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onKeepAliveConnection(KeepAliveConnectionEvent $event): void
    {
        $this->publisher->publish(
            self::PUBLISH_TOPIC,
            [],
            self::PUBLISH_MSG_TYPE
        );
    }
}
