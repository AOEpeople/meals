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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("is_granted('ROLE_USER')")
 */
class EventController extends BaseListController
{
    private DayRepositoryInterface $dayRepo;
    private EntityManagerInterface $em;
    private EventRepositoryInterface $eventRepo;
    private EventDispatcherInterface $eventDispatcher;
    private EventParticipationService $eventPartSrv;

    public function __construct(
        DayRepositoryInterface $dayRepoInterface,
        EntityManagerInterface $entityManager,
        EventRepositoryInterface $eventRepository,
        EventDispatcherInterface $eventDispatcher,
        EventParticipationService $eventPartSrv
    ) {
        $this->dayRepo = $dayRepoInterface;
        $this->em = $entityManager;
        $this->eventRepo = $eventRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->eventPartSrv = $eventPartSrv;
    }

    public function getEventList(): JsonResponse
    {
        $events = $this->eventRepo->findBy(['deleted' => 0]);

        return new JsonResponse($events, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }

    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function new(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);
        if (false === isset($parameters['title']) || false === isset($parameters['public'])) {
            return new JsonResponse(['message' => '701: Event creation parameters are not set'], \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event = new Event($parameters['title'], $parameters['public']);
        $this->em->persist($event);
        $this->em->flush();

        return new JsonResponse(null, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }

    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
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

            $this->em->persist($event);
            $this->em->flush();

            return new JsonResponse($event, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage(), 500]);
        }
    }

    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function delete(Event $event): JsonResponse
    {
        try {
            $event->setDeleted(true);

            $this->em->persist($event);
            $this->em->flush();

            return new JsonResponse(null, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage(), 500]);
        }
    }

    public function join(DateTime $date): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(['message' => '801: User is not allowed to join'], \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN);
        }

        $day = $this->dayRepo->getDayByDate($date);

        $eventParticipation = $this->eventPartSrv->join($profile, $day);
        if (null === $eventParticipation) {
            return new JsonResponse(['message' => '802: User could not join the event'], \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->eventDispatcher->dispatch(new EventParticipationUpdateEvent($eventParticipation));

        return new JsonResponse($this->eventPartSrv->getEventParticipationData($day, $profile), \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }

    public function leave(DateTime $date): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(['message' => '801: User is not allowed to leave'], \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN);
        }

        $day = $this->dayRepo->getDayByDate($date);

        $eventParticipation = $this->eventPartSrv->leave($profile, $day);
        if (null === $eventParticipation) {
            return new JsonResponse(['message' => '802: User could not leave the event'], \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->eventDispatcher->dispatch(new EventParticipationUpdateEvent($eventParticipation));

        return new JsonResponse($this->eventPartSrv->getEventParticipationData($day, $profile), \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }

    public function getEventParticipants(DateTime $date): JsonResponse
    {
        $day = $this->dayRepo->getDayByDate($date);

        if (null === $day) {
            return new JsonResponse(['message' => 'Could not find day'], \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->eventPartSrv->getParticipants($day), \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }
}
