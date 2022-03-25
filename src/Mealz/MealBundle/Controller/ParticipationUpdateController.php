<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DayRepository;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\SlotRepository;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\Service\MealAvailabilityService;
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

    public function getParticipationCountStatus(
        WeekRepository $weekRepository,
        MealAvailabilityService $availabilityService
    ): JsonResponse {
        $participation = [];
        /** @var list<Week|null> $weeks */
        $weeks = [$weekRepository->getCurrentWeek(), $weekRepository->getNextWeek()];

        foreach ($weeks as $week) {
            if (null === $week) {
                continue;
            }

            $participationByDays = ParticipationCountService::getParticipationByDays($week, true);
            if (!empty($participationByDays)) {
                /** @var Day $day */
                foreach ($week->getDays() as $day) {
                    $fmtDate = $day->getDateTime()->format('Y-m-d');
                    /** @var Meal $meal */
                    foreach ($day->getMeals() as $meal) {
                        $participationByDays[$fmtDate]['countByMealIds'][$meal->getId()]['available'] = $availabilityService->isAvailable($meal);
                        $participationByDays[$fmtDate]['countByMealIds'][$meal->getId()]['open'] = $meal->isOpen();
                    }
                }

                $participation[] = $participationByDays;
            }
        }

        if (empty($participation)) {
            return new JsonResponse(null, 204);
        }

        return new JsonResponse(array_merge([], ...$participation));
    }

    public function getParticipationCountStatusOn(
        DateTime $date,
        DayRepository $dayRepository,
        MealAvailabilityService $availabilityService
    ): JsonResponse {
        $day = $dayRepository->getDayByDate($date);
        if (null === $day) {
            return new JsonResponse(null, 404);
        }

        $participation = ParticipationCountService::getParticipationByDay($day);
        if (empty($participation[ParticipationCountService::PARTICIPATION_COUNT_KEY])) {
            return new JsonResponse(null, 204);
        }

        $fmtDate = $day->getDateTime()->format('Y-m-d');
        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            $participation[$fmtDate]['countByMealIds'][$meal->getId()]['available'] = $availabilityService->isAvailable($meal);
            $participation[$fmtDate]['countByMealIds'][$meal->getId()]['open'] = $meal->isOpen();
        }

        return new JsonResponse($participation[ParticipationCountService::PARTICIPATION_COUNT_KEY]);
    }
}
