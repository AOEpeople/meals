<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Slot;
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
            $response[$week->getId()] = [
                'startDate' => $week->getStartTime(),
                'endDate' => $week->getEndTime(),
                'days' => [],
            ];
            /* @var Day $day */
            foreach ($week->getDays() as $day) {
                $activeSlot = $this->participationSrv->getSlot($profile, $day->getDateTime());
                if ($activeSlot !== null) {
                    $activeSlot = $activeSlot->getId();
                } else {
                    $activeSlot = -1;
                }

                $activeParticipations = $this->participationSrv->getCountOfActiveParticipationsByDayAndUser($day, $profile);
                $response[$week->getId()]['days'][$day->getId()] = [
                    'date' => $day->getDateTime(),
                    'isLocked' => $day->getLockParticipationDateTime() < new DateTime(),
                    'activeSlot' => $activeSlot,
                    'slots' => [],
                    'meals' => [],
                ];
                $slots = $this->slotSrv->getSlotStatusForDay($day->getDateTime());
                $this->addSlots($response[$week->getId()]['days'][$day->getId()]['slots'], $slots, $activeParticipations);
                /* @var Meal $meal */
                foreach ($day->getMeals() as $meal) {
                    if($meal->getDish() instanceof DishVariation) {
                        $this->addMealWithVariations($meal, $profile, $response[$week->getId()]['days'][$day->getId()]['meals']);
                    } else {
                        $response[$week->getId()]['days'][$day->getId()]['meals'][$meal->getId()] = $this->convertMealForDashboard($meal, $profile);
                    }
                }
            }
        }

        return new JsonResponse(['weeks' => $response]);
    }

    private function addSlots(array &$slotArray, array $slots, int $activeParticipations): void
    {
        $slotArray[-1] = [
            'id' => -1,
            'title' => 'auto',
            'count' => 0,
            'limit' => 0,
            'slug' => 'auto',
            'disabled' => $activeParticipations > 0
        ];
        foreach ($slots as $slot) {
            $slotArray[$slot['id']] = [
                'id' => $slot['id'],
                'title' => $slot['title'],
                'count' => $slot['count'],
                'limit' => $slot['limit'],
                'slug' => $slot['slug'],
                'disabled' => false
            ];
        }
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
     * Send TimeSlot Data for logged-in cook.
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function getTimeSlotData(): JsonResponse
    {
        $slots = $this->slotSrv->getAllSlots();

        $response = [];

        /** @var Slot $slot */
        foreach ($slots as $slot) {
            $response[$slot->getId()] = [
                'title' => $slot->getTitle(),
                'limit' => $slot->getLimit(),
                'order' => $slot->getOrder(),
                'enabled' => $slot->isEnabled(),
            ];
        }

        return new JsonResponse($response, 200);
    }

    /**
     * @throws Exception
     */
    private function convertMealForDashboard(Meal $meal, Profile $profile): array
    {
        $description = null;
        $parentId = null;
        if (!$meal->getDish() instanceof DishVariation) {
            $description = [
                'en' => $meal->getDish()->getDescriptionEn(),
                'de' => $meal->getDish()->getDescriptionDe(),
            ];
        } else {
            $parentId = $meal->getDish()->getParent()->getId();
        }

        return [
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
            'parentId' => $parentId,
            'participations' => $meal->getParticipants()->count(),
            'isParticipating' => $this->participationSrv->isUserParticipating($meal, $profile),
        ];
    }

    /**
     * @throws Exception
     */
    private function addMealWithVariations(Meal $meal, Profile $profile, array &$meals): void
    {
        $parent = $meal->getDish()->getParent();
        $parentExistsInArray = array_key_exists($parent->getId(), $meals);

        if(!$parentExistsInArray) {
            $meals[$parent->getId()] = [
                'title' => [
                    'en' => $parent->getTitleEn(),
                    'de' => $parent->getTitleDe(),
                ],
                'isNew' => $this->dishSrv->isNew($parent),
                'variations' => []
            ];
        }

        $meals[$parent->getId()]['variations'][$meal->getId()] = $this->convertMealForDashboard($meal, $profile);
    }
}
