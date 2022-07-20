<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Service\ApiService;
use App\Mealz\MealBundle\Service\DishService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\MealBundle\Service\SlotService;
use App\Mealz\MealBundle\Service\WeekService;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends BaseController
{
    private DishService $dishSrv;
    private SlotService $slotSrv;
    private WeekService $weekSrv;
    private ParticipationService $participationSrv;
    private ApiService $apiSrv;

    public function __construct(
        DishService $dishSrv,
        SlotService $slotSrv,
        WeekService $weekSrv,
        ParticipationService $participationSrv,
        ApiService $apiSrv
    ) {
        $this->dishSrv = $dishSrv;
        $this->slotSrv = $slotSrv;
        $this->weekSrv = $weekSrv;
        $this->participationSrv = $participationSrv;
        $this->apiSrv = $apiSrv;
    }

    /**
     * Send Dashboard Data.
     *
     * @throws Exception
     */
    public function getDashboardData(): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, 403);
        }

        $weeks = $this->weekSrv->getNextTwoWeeks();

        $response = [];
        /* @var Week $week */
        foreach ($weeks as $week) {
            $days = [];
            /* @var Day $day */
            foreach ($week->getDays() as $day) {
                $activeSlot = $this->participationSrv->getSlot($profile, $day->getDateTime());
                if ($activeSlot) {
                    $activeSlot = $activeSlot->getId();
                }
                $slots = $this->slotSrv->getSlotStatusForDay($day->getDateTime());
                array_unshift($slots, ['id' => -1, 'title' => 'auto', 'count' => 0, 'limit' => 0, 'slug' => 'auto']);

                $meals = [];
                /* @var Meal $meal */
                foreach ($day->getMeals() as $meal) {
                    if ($meal->getDish() instanceof DishVariation) {
                        $this->addMealWithVariations($meal, $profile, $meals);
                    } else {
                        $meals[] = $this->convertMealForDashboard($meal, $profile);
                    }
                }
                $days[] = [
                    'id' => $day->getId(),
                    'meals' => $meals,
                    'date' => $day->getDateTime(),
                    'slots' => $slots,
                    'activeSlot' => $activeSlot,
                ];
            }
            $response[] = [
                'id' => $week->getId(),
                'days' => $days,
            ];
        }

        return new JsonResponse(['weeks' => $response]);
    }

    /**
     * Send transactions for logged-in user.
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function getTransactionData(): Response
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, 403);
        }

        $dateFrom = new DateTime('-28 days 00:00:00');
        $dateTo = new DateTime();

        list($costDifference, $transactionHistory) = $this->apiSrv->getFullTransactionHistory($dateFrom, $dateTo, $profile);

        usort($transactionHistory, function ($transactionA, $transactionB) {
            return $transactionA['timestamp'] <=> $transactionB['timestamp'];
        });

        return new JsonResponse([
            'data' => $transactionHistory,
            'difference' => $costDifference,
        ]);
    }

    /**
     * @throws Exception
     */
    private function convertMealForDashboard(Meal $meal, Profile $profile): array
    {
        $description = null;
        if (!$meal->getDish() instanceof DishVariation) {
            $description = [
                'en' => $meal->getDish()->getDescriptionEn(),
                'de' => $meal->getDish()->getDescriptionDe(),
            ];
        }

        return [
            'id' => $meal->getId(),
            'title' => [
                'en' => $meal->getDish()->getTitleEn(),
                'de' => $meal->getDish()->getTitleDe(),
            ],
            'description' => $description,
            'dishSlug' => $meal->getDish()->getSlug(),
            'price' => $meal->getPrice(),
            'limit' => $meal->getParticipationLimit(),
            'reachedLimit' => $meal->hasReachedParticipationLimit(),
            'isOpen' => $meal->isOpen(),
            'isLocked' => $meal->isLocked(),
            'isNew' => $this->dishSrv->isNew($meal->getDish()),
            'participations' => $meal->getParticipants()->count(),
            'isParticipating' => $this->participationSrv->isUserParticipating($meal, $profile),
        ];
    }

    /**
     * @throws Exception
     */
    private function addMealWithVariations(Meal $meal, Profile $profile, array &$meals): void
    {
        $parentExistsInArray = false;

        /* @var Meal $addedMeal */
        foreach ($meals as $id => $addedMeal) {
            if ($addedMeal['id'] === $meal->getDish()->getParent()->getId()) {
                $meals[$id]['variations'][] = $this->convertMealForDashboard($meal, $profile);
                $parentExistsInArray = true;
                break;
            }
        }
        if (!$parentExistsInArray) {
            $meals[] = [
                'id' => $meal->getDish()->getParent()->getId(),
                'title' => [
                    'en' => $meal->getDish()->getParent()->getTitleEn(),
                    'de' => $meal->getDish()->getParent()->getTitleDe(),
                ],
                'variations' => [$this->convertMealForDashboard($meal, $profile)],
            ];
        }
    }
}
