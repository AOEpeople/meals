<?php

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Repository\EventParticipationRepositoryInterface;
use App\Mealz\MealBundle\Repository\EventRepositoryInterface;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;

class EventParticipationService
{
    private EntityManagerInterface $em;
    private EventParticipationRepositoryInterface $eventPartRepo;
    private EventRepositoryInterface $eventRepo;

    public function __construct(
        EntityManagerInterface $em,
        EventRepositoryInterface $eventRepo,
        EventParticipationRepositoryInterface $eventPartRepo
    ) {
        $this->em = $em;
        $this->eventPartRepo = $eventPartRepo;
        $this->eventRepo = $eventRepo;
    }

    /**
     * Creates a new eventparticipation or edits the current eventparticipation
     * if an eventId is passed in as a parameter. If no eventId is present
     * the eventparticipation will get removed from the day.
     */
    public function handleEventParticipation(Day $day, int $eventId = null)
    {
        if (null === $eventId) {
            $this->removeEventFromDay($day);
        } else {
            $event = $this->eventRepo->find($eventId);
            $this->addEventToDay($day, $event);
        }
    }

    public function getEventParticipationData(Day $day, Profile $profile): ?array
    {
        $eventParticipation = $day->getEvent();
        if (null === $eventParticipation) {
            return null;
        }

        return [
            'eventId' => $eventParticipation->getEvent()->getId(),
            'participations' => count($eventParticipation->getParticipants()),
            'isParticipating' => null !== $eventParticipation->getParticipant($profile),
        ];
    }

    private function addEventToDay(Day $day, ?Event $event)
    {
        // new eventparticipation
        if (null !== $event && null === $day->getEvent()) {
            $eventParticipation = new EventParticipation($day, $event);
            $day->setEvent($eventParticipation);
        } elseif (null !== $event && $day->getEvent()->getEvent()->getId() !== $event->getId()) {
            // edit eventparticipation
            $day->getEvent()->setEvent($event);
        }
    }

    private function removeEventFromDay(Day $day)
    {
        if (null !== $day->getEvent()) {
            $this->em->remove($day->getEvent());
            $day->setEvent(null);
        }
    }
}