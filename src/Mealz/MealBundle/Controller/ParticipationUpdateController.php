<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepositoryInterface;
use App\Mealz\MealBundle\Service\ParticipationService;
use DateTime;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ParticipationUpdateController extends BaseController
{
    /**
     * @ParamConverter("date", options={"format": "!Y-m-d"})
     */
    public function updateSlot(
        Request $request,
        DateTime $date,
        EventDispatcherInterface $eventDispatcher,
        SlotRepositoryInterface $slotRepo,
        ParticipationService $participationSrv,
        DayRepositoryInterface $dayRepo
    ): JsonResponse {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, 403);
        }

        $slotSlug = $request->request->get('slot', null);
        if (null === $slotSlug) {
            return new JsonResponse(null, 400);
        }

        $newSlot = $slotRepo->findOneBy(['slug' => $slotSlug, 'disabled' => 0, 'deleted' => 0]);
        if (null === $newSlot) {
            return new JsonResponse(null, 422);
        }

        $prevSlot = $participationSrv->getSlot($profile, $date);
        $participationSrv->updateSlot($profile, $date, $newSlot);
        $day = $dayRepo->getDayByDate($date);
        $eventDispatcher->dispatch(new SlotAllocationUpdateEvent($day, $newSlot, $prevSlot));

        return new JsonResponse(null, 200);
    }
}
