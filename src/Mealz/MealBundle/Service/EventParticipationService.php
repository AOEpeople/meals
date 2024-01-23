<?php

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Repository\EventParticipationRepositoryInterface;
use App\Mealz\MealBundle\Repository\EventRepositoryInterface;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;

class EventParticipationService
{
    private Doorman $doorman;
    private EntityManagerInterface $em;
    private EventParticipationRepositoryInterface $eventPartRepo;
    private EventRepositoryInterface $eventRepo;

    public function __construct(
        Doorman $doorman,
        EntityManagerInterface $em,
        EventRepositoryInterface $eventRepo,
        EventParticipationRepositoryInterface $eventPartRepo
    ) {
        $this->doorman = $doorman;
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

    public function getEventParticipationData(Day $day, Profile $profile = null): ?array
    {
        $eventParticipation = $day->getEvent();
        if (null === $eventParticipation) {
            return null;
        }

        $participationData = [
            'eventId' => $eventParticipation->getEvent()->getId(),
            'participationId' => $eventParticipation->getId(),
            'participations' => count($eventParticipation->getParticipants()),
        ];

        if (null !== $profile) {
            $participationData['isParticipating'] = null !== $eventParticipation->getParticipant($profile);
        }

        return $participationData;
    }

    public function join(Profile $profile, Day $day): ?EventParticipation
    {
        $eventParticipation = $day->getEvent();
        if (null !== $eventParticipation && true === $this->doorman->isUserAllowedToJoinEvent($eventParticipation)) {
            $participation = $this->createEventParticipation($profile, $eventParticipation);
            if (null !== $participation) {
                $this->em->persist($participation);
                $this->em->flush();

                return $eventParticipation;
            }
        }

        return null;
    }

    public function leave(Profile $profile, Day $day): ?EventParticipation
    {
        $eventParticipation = $day->getEvent();
        $participation = $eventParticipation->getParticipant($profile);

        $this->em->remove($participation);
        $this->em->flush();

        return $eventParticipation;
    }

    public function getParticipants(Day $day): array
    {
        $eventParticipation = $day->getEvent();
        if (null === $eventParticipation) {
            return [];
        }

        return array_map(
            fn (Participant $participant) => $participant->getProfile()->getFullName(),
            $day->getEvent()->getParticipants()->toArray()
        );
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

    private function createEventParticipation(Profile $profile, EventParticipation $eventParticiation): ?Participant
    {
        return new Participant($profile, null, $eventParticiation);
    }
}