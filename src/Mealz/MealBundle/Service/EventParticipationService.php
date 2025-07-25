<?php

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Repository\EventPartRepoInterface;
use App\Mealz\MealBundle\Repository\EventRepositoryInterface;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class EventParticipationService
{
    private Doorman $doorman;
    private EntityManagerInterface $em;
    private EventPartRepoInterface $eventPartRepo;
    private EventRepositoryInterface $eventRepo;
    private GuestParticipationService $guestPartSrv;

    public function __construct(
        Doorman $doorman,
        EntityManagerInterface $em,
        EventRepositoryInterface $eventRepo,
        EventPartRepoInterface $eventPartRepo,
        GuestParticipationService $guestPartSrv
    ) {
        $this->doorman = $doorman;
        $this->em = $em;
        $this->eventPartRepo = $eventPartRepo;
        $this->eventRepo = $eventRepo;
        $this->guestPartSrv = $guestPartSrv;
    }

    /**
     * Creates a new eventparticipation or edits the current eventparticipation
     * if an eventId is passed in as a parameter. If no eventId is present
     * the eventparticipation will get removed from the day.
     */
    public function handleEventParticipation(Day $day, EventParticipation $event): void
    {
        if (null === $event->getId()) {
            $this->removeEventFromDay($day, $event);
        } else {
            $this->addEventToDay($day, $event);
        }
    }

    /**
     * @return (Day|bool|int|null)[]|null
     *
     * @psalm-return array{day: Day, eventId: int, isParticipating?: bool, isPublic: bool, participationId: int|null, participations: int<0, max>} | array{EventParticipation}
     */
    public function getEventParticipationData(Day $day, ?int $eventId = null, ?Profile $profile = null): ?array
    {
        if (null === $eventId) {
            return $day->getEvents()->toArray();
        } else {
            $eventParticipation = $day->getEvent($eventId);
            if (null === $eventParticipation) {
                return null;
            }
            $participationData = [
                'eventId' => $eventParticipation->getEvent()->getId(),
                'participationId' => $eventParticipation->getId(),
                'participations' => count($eventParticipation->getParticipants()),
                'isPublic' => $eventParticipation->getEvent()->isPublic(),
                'day' => $eventParticipation->getDay(),
            ];

            if (null !== $profile) {
                $participationData['isParticipating'] = null !== $eventParticipation->getParticipant($profile);
            }

            return $participationData;
        }
    }

    public function join(Profile $profile, Day $day, int $eventId): ?EventParticipation
    {
        $eventParticipation = $day->getEvent($eventId);
        if (null !== $eventParticipation && true === $this->doorman->isUserAllowedToJoinEvent($eventParticipation)) {
            $participation = $this->createEventParticipation($profile, $eventParticipation);
            $this->em->persist($participation);
            $this->em->flush();

            return $eventParticipation;
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function joinAsGuest(
        string $firstName,
        string $lastName,
        string $company,
        Day $eventDay,
        EventParticipation $eventParticipation,
    ): EventParticipation {
        $guestProfile = $this->guestPartSrv->getCreateGuestProfile(
            $firstName,
            $lastName,
            $company,
            $eventDay->getDateTime()
        );

        $this->em->beginTransaction();

        try {
            $this->em->persist($guestProfile);
            $participation = $this->createEventParticipation($guestProfile, $eventParticipation);

            $this->em->persist($participation);

            $this->em->flush();
            $this->em->commit();

            return $eventParticipation;
        } catch (Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    public function leave(Profile $profile, Day $day, int $eventId): ?EventParticipation
    {
        $eventParticipation = $day->getEvent($eventId);
        $participation = $eventParticipation->getParticipant($profile);

        if (null !== $participation) {
            $this->em->remove($participation);
            $this->em->flush();

            return $eventParticipation;
        }

        return null;
    }

    /**
     * @return string[]
     *
     * @psalm-return array<string>
     */
    public function getParticipants(Day $day, int $eventId): array
    {
        $eventParticipation = $day->getEvent($eventId);
        if (null === $eventParticipation) {
            return [];
        }

        return array_map(
            fn (Participant $participant) => $this->getParticipantName($participant),
            $day->getEvent($eventId)->getParticipants()->toArray()
        );
    }

    /**
     * adds new event to the eventCollection.
     */
    private function addEventToDay(Day $day, ?EventParticipation $event): void
    {
        // new eventparticipation
        if (null !== $event) {
            $eventParticipation = new EventParticipation($day, $event->getEvent());
            $day->addEvent($eventParticipation);
        }
    }

    private function removeEventFromDay(Day $day, EventParticipation $event): void
    {
        $day->removeEvent($event);
    }

    private function createEventParticipation(Profile $profile, EventParticipation $eventParticipation): Participant
    {
        $participant = new Participant($profile, null, $eventParticipation);
        $eventParticipation->setParticipant($participant);
        $this->em->persist($participant);
        $this->em->persist($eventParticipation);
        $this->em->flush();

        return $participant;
    }

    private function getParticipantName(Participant $participant): string
    {
        if ($participant->isGuest()) {
            $company = strlen($participant->getProfile()->getCompany()) > 0 ?
                ' (' . $participant->getProfile()->getCompany() . ')' :
                ' (Gast)';

            return $participant->getProfile()->getFullName() . $company;
        } else {
            return $participant->getProfile()->getFullName();
        }
    }
}
