<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Event\Subscriber;

use App\Mealz\AccountingBundle\Event\ProfileSettlementEvent;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SettlementSubscriber implements EventSubscriberInterface
{
    private ParticipantRepositoryInterface $participantRepo;

    public function __construct(ParticipantRepositoryInterface $participantRepo)
    {
        $this->participantRepo = $participantRepo;
    }

    /**
     * @return string[]
     *
     * @psalm-return array{ProfileSettlementEvent::class: 'onProfileSettlement'}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ProfileSettlementEvent::class => 'onProfileSettlement',
        ];
    }

    public function onProfileSettlement(ProfileSettlementEvent $event): void
    {
        $this->participantRepo->removeFutureMealsByProfile($event->getProfile());
    }
}
