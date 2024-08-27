<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Service\ApiService;
use App\Mealz\MealBundle\Service\DishService;
use App\Mealz\MealBundle\Service\GuestParticipationService;
use App\Mealz\MealBundle\Service\OfferService;
use App\Mealz\MealBundle\Service\ParticipationCountService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\MealBundle\Service\SlotService;
use App\Mealz\MealBundle\Service\WeekService;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\MealBundle\Service\EventParticipationService;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends BaseController
{
    private DishService $dishSrv;
    private SlotService $slotSrv;
    private WeekService $weekSrv;
    private ParticipationService $participationSrv;
    private ApiService $apiSrv;
    private OfferService $offerSrv;
    private GuestParticipationService $guestPartiSrv;
    private EventParticipationService $eventService;

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
        ], Response::HTTP_OK);
    }

    /**
     * Send Dashboard Data.
     */
    public function getDashboardData(ParticipationCountService $partCountSrv): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $weeks = $this->weekSrv->getNextTwoWeeks();

        $response = [];

        /* @var Week $week */
        foreach ($weeks as $week) {
            $response[$week->getId()] = [
                'startDate' => $week->getStartTime(),
                'endDate' => $week->getEndTime(),
                'days' => [],
                'isEnabled' => $week->isEnabled(),
            ];
            /** @var Day $day */
            foreach ($week->getDays() as $day) {
                $participationsPerDay = $partCountSrv->getParticipationByDay($day);

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
                    'isEnabled' => $day->isEnabled(),
                    'events' => [],
                ];
                $events = [];
                foreach($day->getEvents() as $event){
                    $events = $this->setEventData($event, $events);
                }
                $response[$week->getId()]['days'][$day->getId()]['events'] = $events;
                $this->addSlots($response[$week->getId()]['days'][$day->getId()]['slots'], $slots, $day, $activeParticipations);
                /** @var Meal $meal */
                foreach ($day->getMeals() as $meal) {
                    $participationCount = null;
                    if (array_key_exists($meal->getDish()->getSlug(), $participationsPerDay['totalCountByDishSlugs'])) {
                        $participationCount = $participationsPerDay['totalCountByDishSlugs'][$meal->getDish()->getSlug()]['count'];
                    } else {
                        $participationCount = $meal->getParticipants()->count();
                    }

                    if ($meal->getDish() instanceof DishVariation) {
                        $this->addMealWithVariations($meal, $participationCount, $profile, $response[$week->getId()]['days'][$day->getId()]['meals']);
                    } else {
                        $response[$week->getId()]['days'][$day->getId()]['meals'][$meal->getId()] = $this->convertMealForDashboard($meal, $participationCount, $profile);
                    }
                }
            }
        }

        return new JsonResponse(['weeks' => $response]);
    }

    public function getNextThreeDays(): JsonResponse
    {
        $result = [];

        $today = new DateTime('today');

        for ($i = 0; $i < 3; $i = $i + 1) {
            $today->modify('+1 weekday');
            $meals = $this->apiSrv->findAllOn($today);
            $dishes = ['en' => [], 'de' => []];

            $uniqueMeals = $this->dishSrv->getUniqueDishesFromMeals($meals);

            foreach ($uniqueMeals as $dish) {
                $dishes['en'][] = [
                    'title' => $dish->getTitleEn(),
                    'diet' => $dish->getDiet(),
                ];
                $dishes['de'][] = [
                    'title' => $dish->getTitleDe(),
                    'diet' => $dish->getDiet(),
                ];
            }

            $result[$today->format('Y-m-d')] = $dishes;
        }

        return new JsonResponse($result, Response::HTTP_OK);
    }

    public function list(): JsonResponse
    {
        $day = $this->apiSrv->getDayByDate(new DateTime('today'));

        if (null === $day) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        if (false === $day->isEnabled() || false === $day->getWeek()->isEnabled()) {
            $list['day'] = $day->getDateTime();

            return new JsonResponse($list, Response::HTTP_OK);
        }

        $list['data'] = $this->participationSrv->getParticipationListBySlots($day);

        $meals = $this->participationSrv->getMealsForTheDay($day);

        $list['meals'] = [];

        foreach ($meals as $meal) {
            $list['meals'] = $list['meals'] + $this->getDishData($meal);
        }
        $list['events'] =  $this->eventService->getEventParticipationData($day);
        $list['day'] = $day->getDateTime();

        return new JsonResponse($list, Response::HTTP_OK);
    }

    public function listByDate(DateTime $date): JsonResponse
    {
        $day = $this->apiSrv->getDayByDate($date);
        if (null === $day) {
            return new JsonResponse(['message' => 'Day not found'], 404);
        }
        $list['data'] = $this->participationSrv->getParticipationListBySlots($day);
        $list['day'] = $day->getDateTime();

        return new JsonResponse($list, 200);
    }

    public function listParticipantsByDate(DateTime $date): JsonResponse
    {
        $day = $this->apiSrv->getDayByDate($date);
        if (null === $day) {
            return new JsonResponse(['message' => 'Day not found'], 404);
        }

        $participants = $this->participationSrv->getParticipationList($day);

        return new JsonResponse($participants, 200);
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
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
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

    private function convertMealForDashboard(Meal $meal, float $participationCount, ?Profile $profile): array
    {
        $description = null;
        $parentId = null;
        $participationId = null;
        $isOffering = false;

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
        }

        $reachedLimit = $meal->getParticipationLimit() > 0.0 ? $participationCount >= $meal->getParticipationLimit() : false;

        if ($meal->isCombinedMeal()) {
            $reachedLimit = $this->apiSrv->hasCombiReachedLimit($meal->getDay());
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
            'reachedLimit' => $reachedLimit,
            'isOpen' => $meal->isOpen(),
            'isLocked' => $meal->isLocked(),
            'isNew' => $this->dishSrv->isNew($meal->getDish()),
            'parentId' => $parentId,
            'participations' => $participationCount,
            'isParticipating' => $participationId,
            'hasOffers' => $this->offerSrv->getOfferCountByMeal($meal) > 0,
            'isOffering' => $isOffering,
            'diet' => $meal->getDish()->getDiet(),
        ];
    }

    private function addMealWithVariations(Meal $meal, float $participationCount, ?Profile $profile, array &$meals
    ): void {
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
                'diet' => $meal->getDish()->getDiet(),
            ];
        }

        $meals[$parent->getId()]['variations'][$meal->getId()] = $this->convertMealForDashboard($meal, $participationCount, $profile);
    }

    public function getGuestData(string $guestInvitationId, ParticipationCountService $partCountSrv): JsonResponse
    {
        $guestInvitation = $this->guestPartiSrv->getGuestInvitationById($guestInvitationId);
        if (null === $guestInvitation) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $day = $guestInvitation->getDay();
        $slots = $this->slotSrv->getAllActiveSlots();
        $participationsPerDay = $partCountSrv->getParticipationByDay($day);

        $guestData = [
            'date' => $day->getDateTime(),
            'isLocked' => $day->getLockParticipationDateTime() < new DateTime(),
            'activeSlot' => 0,
            'slotsEnabled' => count($slots) > 0,
            'slots' => [],
            'meals' => [],
            'isEnabled' => $day->isEnabled(),
        ];

        $this->addSlots($guestData['slots'], $slots, $day, 0);

        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            $participationCount = null;
            if (array_key_exists($meal->getDish()->getSlug(), $participationsPerDay['totalCountByDishSlugs'])) {
                $participationCount = $participationsPerDay['totalCountByDishSlugs'][$meal->getDish()->getSlug()]['count'];
            } else {
                $participationCount = $meal->getParticipants()->count();
            }

            if (true === ($meal->getDish() instanceof DishVariation)) {
                $this->addMealWithVariations($meal, $participationCount, null, $guestData['meals']);
            } else {
                $guestData['meals'][$meal->getId()] = $this->convertMealForDashboard($meal, $participationCount, null);
            }
        }

        return new JsonResponse($guestData, Response::HTTP_OK);
    }

    /**
     * @return ((int|string[]|null)[]|mixed)[]
     *
     * @psalm-return array<array{title: array{en: string, de: string}, parent?: int|null, participations?: int}|mixed>
     */
    private function getDishData(Meal $meal): array
    {
        $collection[$meal->getDish()->getId()] = [
            'title' => [
                'en' => $meal->getDish()->getTitleEn(),
                'de' => $meal->getDish()->getTitleDe(),
            ],
            'parent' => $meal->getDish()->getParent() ? $meal->getDish()->getParent()->getId() : null,
            'participations' => $this->participationSrv->getCountByMeal($meal, true),
            'diet' => $meal->getDish()->getDiet(),
        ];

        if (null != $meal->getDish()->getParent()) {
            $collection[$meal->getDish()->getParent()->getId()] = [
                'title' => [
                    'en' => $meal->getDish()->getParent()->getTitleEn(),
                    'de' => $meal->getDish()->getParent()->getTitleDe(),
                ],
                'diet' => $meal->getDish()->getDiet(),
            ];
        }

        return $collection;
    }
    private function setEventData(EventParticipation $event, array $events): array
    {
        $events[$event->getId()] = [
            'id' => $event->getId(),
            'event' => $event->getEvent(),
            'day' => $event->getDay()->getId(),
            'participants' => $event->getParticipants(),
        ];
        return $events;
    }
}
