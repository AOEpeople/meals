<?php
declare(strict_types=1);
namespace App\Mealz\AccountingBundle\Event\Subscriber;

use App\Mealz\AccountingBundle\Event\ProfileSettlementEvent;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SettlementSubscriber implements EventSubscriberInterface
{

    private ParticipantRepository $participantRepo;

    public function __construct(ParticipantRepository $participantRepo){
        $this->participantRepo = $participantRepo;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProfileSettlementEvent::NAME => 'onProfileSettlement'
        ];
    }

    /**
     * @param ProfileSettlementEvent $event
     */
    public function onProfileSettlement(ProfileSettlementEvent $event) {
        $this->participantRepo->removeFutureMealsByProfile($event->getProfile());
    }
}