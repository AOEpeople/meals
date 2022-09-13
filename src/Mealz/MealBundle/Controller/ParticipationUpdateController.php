<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepositoryInterface;
use App\Mealz\MealBundle\Service\ParticipationService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ParticipationUpdateController extends BaseController
{
    public function updateSlot(
        Request $request,
        EventDispatcherInterface $eventDispatcher,
        SlotRepositoryInterface $slotRepo,
        ParticipationService $participationSrv,
        DayRepositoryInterface $dayRepo
    ): JsonResponse {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, 403);
        }

        $newSlot = null;

        $parameters = json_decode($request->getContent(), true);
        if (-1 !== $parameters['slotID']) {
            $newSlot = $slotRepo->find($parameters['slotID']);
        }
        /** @var Day $day */
        $day = $dayRepo->find($parameters['dayID']);

        if (null === $newSlot || null === $day) {
            return new JsonResponse(null, 422);
        }

        $prevSlot = $participationSrv->getSlot($profile, $day->getDateTime());
        $participationSrv->updateSlot($profile, $day->getDateTime(), $newSlot);
        $eventDispatcher->dispatch(new SlotAllocationUpdateEvent($day, $newSlot, $prevSlot));

        return new JsonResponse(null, 200);
    }
}
