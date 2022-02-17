<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Event\UpdateCountEvent;
use App\Mealz\MealBundle\Service\Publisher\MercurePublisher;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CountUpdateSubscriber implements EventSubscriberInterface
{
    private MercurePublisher $mercureHub;
    private LoggerInterface $logger;

    /**
     * @param MercurePublisher $mercureHub
     * @param LoggerInterface $logger
     */
    public function __construct(MercurePublisher $mercureHub, LoggerInterface $logger)
    {
        $this->mercureHub = $mercureHub;
        $this->logger = $logger;
    }


    public static function getSubscribedEvents() : array
    {
        return [
            UpdateCountEvent::class => 'onUpdateCount',
        ];
    }

    public function onUpdateCount(UpdateCountEvent $event): void
    {
        $count = $event->getMeal()->getParticipants()->count();

        if($event->getProfile() !== null && $event->getProfile()->isGuest()) {
            $count++;
        }

        $success = $this->mercureHub->publish(['/join', '/delete'], json_encode(
            [
                'id'    => $event->getMeal()->getId(),
                'count' => $count
            ]));
        if(!$success) {
            $this->logger->error('MercureHub: publishing to topic /join and /delete failed');
        }
    }
}