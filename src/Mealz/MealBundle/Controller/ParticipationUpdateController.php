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

final class ParticipationUpdateController extends BaseController
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
            return new JsonResponse(null, \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN);
        }

        $parameters = json_decode($request->getContent(), true);

        /** @var Day $day */
        $day = $dayRepo->find($parameters['dayID']);

        if (null === $day) {
            return new JsonResponse(null, \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $newSlot = $slotRepo->find($parameters['slotID']);

        $prevSlot = $participationSrv->getSlot($profile, $day->getDateTime());

        if (null != $newSlot) {
            $participationSrv->updateSlot($profile, $day->getDateTime(), $newSlot);
        }
        $eventDispatcher->dispatch(new SlotAllocationUpdateEvent($day, $newSlot, $prevSlot));

        return new JsonResponse(null, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }
}
