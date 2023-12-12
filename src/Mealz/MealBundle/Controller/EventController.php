<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Repository\EventRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class EventController extends BaseListController
{
    private EntityManagerInterface $em;
    private EventRepositoryInterface $eventRepo;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventRepositoryInterface $eventRepository
    ) {
        $this->em = $entityManager;
        $this->eventRepo = $eventRepository;
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
}
