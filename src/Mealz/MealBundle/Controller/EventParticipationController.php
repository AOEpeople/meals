<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Event\EventParticipationUpdateEvent;
use App\Mealz\MealBundle\Service\EventParticipationService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Security("is_granted('ROLE_USER')")
 */
class EventParticipationController extends BaseController
{

    private EventDispatcherInterface $eventDispatcher;
    private EventParticipationService $eventParticipationService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EventParticipationService $eventParticipationService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->eventParticipationService = $eventParticipationService;
    }

    public function join(Day $day): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(['messasge' => '801: User is not allowed to join'], 403);
        }

        $eventParticipation = $this->eventParticipationService->join($profile, $day);
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

        $eventParticipation = $this->eventParticipationService->leave($profile, $day);
        if (null === $eventParticipation) {
            return new JsonResponse(['messasge' => '802: User could not leave the event'], 500);
        }

        $this->eventDispatcher->dispatch(new EventParticipationUpdateEvent($eventParticipation));
        return new JsonResponse(['isParticipating' => false], 200);
    }
}