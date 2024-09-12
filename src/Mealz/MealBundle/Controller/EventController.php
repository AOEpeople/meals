<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Event\EventParticipationUpdateEvent;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\EventRepositoryInterface;
use App\Mealz\MealBundle\Service\EventParticipationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class EventController extends BaseListController
{
    public function __construct(
        private readonly DayRepositoryInterface $dayRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EventParticipationService $eventPartSrv,
        private readonly LoggerInterface $logger
    ) {
    }

    public function getEventList(): JsonResponse
    {
        $events = $this->eventRepository->findBy(['deleted' => 0]);
        $this->logger->info('Anzahl Events:' . count($events) );
        $this->logger->info('Events:' . print_r($events, true));
        return new JsonResponse($events, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_KITCHEN_STAFF')]
    public function new(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);
        if (false === isset($parameters['title']) || false === isset($parameters['public'])) {
            return new JsonResponse(['message' => '701: Event creation parameters are not set'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event = new Event($parameters['title'], $parameters['public']);
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_KITCHEN_STAFF')]
    public function update(Request $request, Event $event): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);

            if (true === isset($parameters['title'])) {
                $event->setTitle($parameters['title']);
            }

            if (true === isset($parameters['public'])) {
                $event->setPublic($parameters['public']);
            }

            $this->entityManager->persist($event);
            $this->entityManager->flush();

            return new JsonResponse($event, Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logger->info('event update error', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage(), 500]);
        }
    }

    #[IsGranted('ROLE_KITCHEN_STAFF')]
    public function delete(Event $event): JsonResponse
    {
        try {
            $event->setDeleted(true);

            $this->entityManager->persist($event);
            $this->entityManager->flush();

            return new JsonResponse(null, Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logger->info('event delete error', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage(), 500]);
        }
    }

    public function join(DateTime $date, int $eventId): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(['message' => '801: User is not allowed to join'], Response::HTTP_FORBIDDEN);
        }

        $day = $this->dayRepository->getDayByDate($date);
        $eventLockModifier = (string) $this->getParameter('mealz.event.lock_participation_at');

        if (new DateTime() > $day->getDateTime()->modify($eventLockModifier)) {
            return new JsonResponse(['message' => '804: User could not join the event'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $eventParticipation = $this->eventPartSrv->join($profile, $day, $eventId);
        if (null === $eventParticipation) {
            return new JsonResponse(['message' => '802: User could not join the event'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->eventDispatcher->dispatch(new EventParticipationUpdateEvent($eventParticipation));

        return new JsonResponse($this->eventPartSrv->getEventParticipationData($day,$eventId, $profile), Response::HTTP_OK);
    }

    public function leave(DateTime $date, int $eventId): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(['message' => '801: User is not allowed to leave'], Response::HTTP_FORBIDDEN);
        }

        $day = $this->dayRepository->getDayByDate($date);

        $eventParticipation = $this->eventPartSrv->leave($profile, $day, $eventId);
        if (null === $eventParticipation) {
            return new JsonResponse(['message' => '802: User could not leave the event'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->eventDispatcher->dispatch(new EventParticipationUpdateEvent($eventParticipation));

        return new JsonResponse($this->eventPartSrv->getEventParticipationData($day,$eventId, $profile), Response::HTTP_OK);
    }

    public function getEventParticipants(DateTime $date, int $eventId): JsonResponse
    {
        $day = $this->dayRepository->getDayByDate($date);

        if (null === $day) {
            return new JsonResponse(['message' => 'Could not find day'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->eventPartSrv->getParticipants($day, $eventId), Response::HTTP_OK);
    }
}
