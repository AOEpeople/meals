<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Event\EventParticipationUpdateEvent;
use App\Mealz\MealBundle\Repository\EventRepositoryInterface;
use App\Mealz\MealBundle\Service\EventParticipationService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class EventController extends BaseListController
{
    private EntityManagerInterface $em;
    private EventRepositoryInterface $eventRepo;
    private EventDispatcherInterface $eventDispatcher;
    private EventParticipationService $eventPartSrv;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventRepositoryInterface $eventRepository,
        EventDispatcherInterface $eventDispatcher,
        EventParticipationService $eventPartSrv
    ) {
        $this->em = $entityManager;
        $this->eventRepo = $eventRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->eventPartSrv = $eventPartSrv;
    }

    public function getEventList(): JsonResponse
    {
        $events = $this->eventRepo->findBy(['deleted' => 0]);

        return new JsonResponse($events, 200);
    }

    public function new(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);
        if (false === isset($parameters['title']) || false === isset($parameters['public'])) {
            return new JsonResponse(['message' => '701: Event creation parameters are not set'], 500);
        }

        $event = new Event($parameters['title'], $parameters['public']);
        $this->em->persist($event);
        $this->em->flush();

        return new JsonResponse(null, 200);
    }

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

            return new JsonResponse($event, 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage(), 500]);
        }
    }

    public function delete(Event $event): JsonResponse
    {
        try {
            $event->setDeleted(true);

            $this->em->persist($event);
            $this->em->flush();

            return new JsonResponse(null, 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage(), 500]);
        }
    }

    public function join(Day $day): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(['messasge' => '801: User is not allowed to join'], 403);
        }

        $eventParticipation = $this->eventPartSrv->join($profile, $day);
        if (null === $eventParticipation) {
            return new JsonResponse(['messasge' => '802: User could not join the event'], 500);
        }

        $this->eventDispatcher->dispatch(new EventParticipationUpdateEvent($eventParticipation));

        return new JsonResponse(['isParticipating' => true], 200);
    }

    public function leave(Day $day): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(['messasge' => '801: User is not allowed to leave'], 403);
        }

        $eventParticipation = $this->eventPartSrv->leave($profile, $day);
        if (null === $eventParticipation) {
            return new JsonResponse(['messasge' => '802: User could not leave the event'], 500);
        }

        $this->eventDispatcher->dispatch(new EventParticipationUpdateEvent($eventParticipation));

        return new JsonResponse(['isParticipating' => false], 200);
    }
}
