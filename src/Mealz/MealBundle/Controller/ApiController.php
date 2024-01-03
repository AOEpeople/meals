<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Service\ApiService;
use App\Mealz\MealBundle\Service\DishService;
use App\Mealz\MealBundle\Service\GuestParticipationService;
use App\Mealz\MealBundle\Service\OfferService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\MealBundle\Service\SlotService;
use App\Mealz\MealBundle\Service\WeekService;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends BaseController
{
    private DishService $dishSrv;
    private SlotService $slotSrv;
    private WeekService $weekSrv;
    private ParticipationService $participationSrv;
    private ApiService $apiSrv;
    private OfferService $offerSrv;
    private GuestParticipationService $guestPartiSrv;

    public function __construct(
        DishService $dishSrv,
        SlotService $slotSrv,
        WeekService $weekSrv,
        ParticipationService $participationSrv,
        ApiService $apiSrv,
        OfferService $offerSrv,
        GuestParticipationService $guestPartiSrv
    ) {
        $this->dishSrv = $dishSrv;
        $this->slotSrv = $slotSrv;
        $this->weekSrv = $weekSrv;
        $this->participationSrv = $participationSrv;
        $this->apiSrv = $apiSrv;
        $this->offerSrv = $offerSrv;
        $this->guestPartiSrv = $guestPartiSrv;
    }

    public function getEnvironmentVars(): JsonResponse
    {
        return new JsonResponse([
            'paypalId' => $this->getParameter('app.paypal.client_id'),
            'mercureUrl' => $this->getParameter('app.pubsub.subscribe_url'),
        ], 200);
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
                if (null !== $activeSlot) {
                    $activeSlot = $activeSlot->getId();
                } else {
                    $activeSlot = 0;
                }

                $slots = $this->slotSrv->getAllActiveSlots();

                $activeParticipations = $this->participationSrv->getCountOfActiveParticipationsByDayAndUser($day->getDateTime(), $profile);
                $response[$week->getId()]['days'][$day->getId()] = [
                    'date' => $day->getDateTime(),
                    'isLocked' => $day->getLockParticipationDateTime() < new DateTime(),
                    'activeSlot' => $activeSlot,
                    'slotsEnabled' => count($slots) > 0,
                    'slots' => [],
                    'meals' => [],
                ];

                $this->addSlots($response[$week->getId()]['days'][$day->getId()]['slots'], $slots, $day, $activeParticipations);
                /* @var Meal $meal */
                foreach ($day->getMeals() as $meal) {
                    if ($meal->getDish() instanceof DishVariation) {
                        $this->addMealWithVariations($meal, $profile, $response[$week->getId()]['days'][$day->getId()]['meals']);
                    } else {
                        $response[$week->getId()]['days'][$day->getId()]['meals'][$meal->getId()] = $this->convertMealForDashboard($meal, $profile);
                    }
                }
            }
        }

        return new JsonResponse(['weeks' => $response]);
    }

    public function getNextThreeDays(): JSONResponse
    {
        $result = [];

        $today = new DateTime('today');

        for ($i = 0; $i < 3; $i = $i + 1) {
            $today->modify('+1 weekday');
            $meals = $this->apiSrv->findAllOn($today);
            $dishes = ['en' => [], 'de' => []];

            $uniqueMeals = $this->dishSrv->getUniqueDishesFromMeals($meals);

            foreach ($uniqueMeals as $dish) {
                $dishes['en'][] = $dish->getTitleEn();
                $dishes['de'][] = $dish->getTitleDe();
            }

            $result[$today->format('Y-m-d')] = $dishes;
        }

        return new JsonResponse($result, 200);
    }

    public function list(): JSONResponse
    {
        $day = $this->apiSrv->getDayByDate(new DateTime('today'));

        $list['data'] = $this->participationSrv->getParticipationListBySlots($day);

        $meals = $this->participationSrv->getMealsForTheDay($day);

        $list['meals'] = [];

        foreach ($meals as $meal) {
            $list['meals'] = $list['meals'] + $this->getDishData($meal);
        }

        $list['day'] = $day->getDateTime();

        return new JsonResponse($list, 200);
    }

    public function listByDate(DateTime $date): JSONResponse
    {
        $day = $this->apiSrv->getDayByDate($date);
        if (null === $day) {
            return new JsonResponse('', 404);
        }
        $list['data'] = $this->participationSrv->getParticipationListBySlots($day);
        $list['day'] = $day->getDateTime();

        return new JsonResponse($list, 200);
    }

    public function listParticipantsByDate(DateTime $date): JSONResponse
    {
        $day = $this->apiSrv->getDayByDate($date);
        if (null === $day) {
            return new JsonResponse('', 404);
        }

        $list = [];
        $data = $this->participationSrv->getParticipationList($day);

        foreach ($data as $participant) {
        $list[] = $participant->getProfile()->getFirstName() .' '. $participant->getProfile()->getName();
        }

        $uniqueArray = array_unique($list);

        return new JsonResponse(array_values($uniqueArray), 200);
    }

    private function addSlots(array &$slotArray, array $slots, Day $day, ?int $activeParticipations): void
    {
        $disabled = false;
        if (null !== $activeParticipations) {
            $disabled = $activeParticipations > 0;
        }

        $slotArray[0] = [
            'id' => 0,
            'title' => 'auto',
            'count' => 0,
            'limit' => 0,
            'slug' => 'auto',
            'disabled' => $disabled,
        ];
        /** @var Slot $slot */
        foreach ($slots as $slot) {
            $slotArray[$slot->getId()] = [
                'id' => $slot->getId(),
                'title' => $slot->getTitle(),
                'count' => $this->slotSrv->getSlotParticipationCountOnDay($day, $slot),
                'limit' => $slot->getLimit(),
                'slug' => $slot->getSlug(),
                'disabled' => false,
            ];
        }
    }

    /**
     * Send transactions for logged-in user.
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function getTransactionData(): JsonResponse
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
    private function convertMealForDashboard(Meal $meal, ?Profile $profile): array
    {
        $description = null;
        $parentId = null;
        $participationId = null;
        $isOffering = false;
        $mealState = 'open';

        if (false === ($meal->getDish() instanceof DishVariation)) {
            $description = [
                'en' => $meal->getDish()->getDescriptionEn(),
                'de' => $meal->getDish()->getDescriptionDe(),
            ];
        } else {
            $parentId = $meal->getDish()->getParent()->getId();
        }

        if (null !== $profile) {
            $participation = $this->participationSrv->getParticipationByMealAndUser($meal, $profile);
            if (null !== $participation) {
                $participationId = $participation->getId();
            }
            $isOffering = $this->offerSrv->isOfferingMeal($profile, $meal);
            $mealState = $this->getMealState($meal, $profile, $participation);
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
            'isParticipating' => $participationId,
            'hasOffers' => $this->offerSrv->getOfferCountByMeal($meal) > 0,
            'isOffering' => $isOffering,
            'mealState' => $mealState,
        ];
    }

    /**
     * @throws Exception
     */
    private function addMealWithVariations(Meal $meal, ?Profile $profile, array &$meals): void
    {
        $parent = $meal->getDish()->getParent();
        $parentExistsInArray = array_key_exists($parent->getId(), $meals);

        if (false === $parentExistsInArray) {
            $meals[$parent->getId()] = [
                'title' => [
                    'en' => $parent->getTitleEn(),
                    'de' => $parent->getTitleDe(),
                ],
                'isNew' => $this->dishSrv->isNew($parent),
                'variations' => [],
                'isLocked' => $meal->isLocked(),
                'isOpen' => $meal->isOpen(),
            ];
        }

        $meals[$parent->getId()]['variations'][$meal->getId()] = $this->convertMealForDashboard($meal, $profile);
    }

    private function getMealState(Meal $meal, Profile $profile, ?Participant $participant): string
    {
        if (true === $meal->isLocked() && true === $meal->isOpen()) {
            $isOffering = $this->offerSrv->isOfferingMeal($profile, $meal);
            if ($isOffering) {
                return 'offering';
            } elseif (null !== $participant) {
                return 'offerable';
            } elseif ($this->offerSrv->getOfferCountByMeal($meal) > 0) {
                return 'tradeable';
            }
        }
        if (false === $meal->isLocked() && true === $meal->isOpen() && false === $meal->hasReachedParticipationLimit()) {
            return 'open';
        }

        return 'disabled';
    }

    /**
     * @throws Exception
     */
    public function getGuestData(string $guestInvitationId): JsonResponse
    {
        $guestInvitation = $this->guestPartiSrv->getGuestInvitationById($guestInvitationId);
        if (null === $guestInvitation) {
            return new JsonResponse(null, 404);
        }

        $day = $guestInvitation->getDay();
        $slots = $this->slotSrv->getAllActiveSlots();

        $guestData = [
            'date' => $day->getDateTime(),
            'isLocked' => $day->getLockParticipationDateTime() < new DateTime(),
            'activeSlot' => 0,
            'slotsEnabled' => count($slots) > 0,
            'slots' => [],
            'meals' => [],
        ];

        $this->addSlots($guestData['slots'], $slots, $day, 0);

        /* @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            if (true === ($meal->getDish() instanceof DishVariation)) {
                $this->addMealWithVariations($meal, null, $guestData['meals']);
            } else {
                $guestData['meals'][$meal->getId()] = $this->convertMealForDashboard($meal, null);
            }
        }

        return new JsonResponse($guestData, 200);
    }

    private function getDishData(Meal $meal): array
    {
        $collection[$meal->getDish()->getId()] = [
            'title' => [
                'en' => $meal->getDish()->getTitleEn(),
                'de' => $meal->getDish()->getTitleDe(),
            ],
            'parent' => $meal->getDish()->getParent() ? $meal->getDish()->getParent()->getId() : null,
            'participations' => $this->participationSrv->getCountByMeal($meal, true),
        ];

        if (null != $meal->getDish()->getParent()) {
            $collection[$meal->getDish()->getParent()->getId()] = [
                'title' => [
                    'en' => $meal->getDish()->getParent()->getTitleEn(),
                    'de' => $meal->getDish()->getParent()->getTitleDe(),
                ],
            ];
        }

        return $collection;
    }
}
