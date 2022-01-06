<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\DayRepository;
use App\Mealz\MealBundle\Entity\SlotRepository;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\Service\ParticipationCountService;
use App\Mealz\MealBundle\Service\ParticipationService;
use DateTime;
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
        SlotRepository $slotRepo,
        ParticipationService $participationSrv
    ): JsonResponse {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, 403);
        }

        $slotSlug = $request->request->get('slot', null);
        if (null === $slotSlug) {
            return new JsonResponse(null, 400);
        }

        $slot = $slotRepo->findOneBy(['slug' => $slotSlug, 'disabled' => 0, 'deleted' => 0]);
        if (null === $slot) {
            return new JsonResponse(null, 422);
        }

        $participationSrv->updateSlot($profile, $date, $slot);

        return new JsonResponse(null, 200);
    }

    public function getSlotStatus(ParticipationService $participationSrv): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, 403);
        }

        $data = $participationSrv->getSlotsStatusFor($profile);

        return new JsonResponse($data);
    }

    public function getSlotStatusOn(DateTime $date, ParticipationService $participationSrv): JsonResponse
    {
        $data = $participationSrv->getSlotsStatusOn($date);

        return new JsonResponse($data);
    }

    public function getParticipationCountStatus(WeekRepository $weekRepository): JsonResponse
    {
        $participations = [];
        /** @var Week $currentWeek */
        $currentWeek = $weekRepository->getCurrentWeek();
        if (null !== $currentWeek) {
            $participationByDays = ParticipationCountService::getParticipationByDays($currentWeek);
            if (!empty($participationByDays)) {
                $participations = $participationByDays;
            }
        }

        $nextWeek = $weekRepository->getNextWeek();
        if (null !== $nextWeek) {
            $participationByDays = ParticipationCountService::getParticipationByDays($nextWeek);
            if (!empty($participationByDays)) {
                $participations = array_merge($participations, $participationByDays);
            }
        }

        if (empty($participations)) {
            return new JsonResponse(null, 204);
        }

        return new JsonResponse($participations);
    }

    public function getParticipationCountStatusOn(DateTime $date, DayRepository $dayRepository): JsonResponse
    {
        $day = $dayRepository->getDayByDate($date);
        if (null === $day) {
            return new JsonResponse(null, 404);
        }

        $participations = ParticipationCountService::getParticipationByDay($day);
        if (empty($participations[ParticipationCountService::PARTICIPATION_COUNT_KEY])) {
            return new JsonResponse(null, 204);
        }

        return new JsonResponse($participations[ParticipationCountService::PARTICIPATION_COUNT_KEY]);
    }
}
