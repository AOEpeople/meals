<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Entity\Week;
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
    private string $paypalId;

    public function __construct(
        DishService $dishSrv,
        SlotService $slotSrv,
        WeekService $weekSrv,
        ParticipationService $participationSrv,
        ApiService $apiSrv,
        OfferService $offerSrv,
        GuestParticipationService $guestPartiSrv,
        string $paypalId
    ) {
        $this->dishSrv = $dishSrv;
        $this->slotSrv = $slotSrv;
        $this->weekSrv = $weekSrv;
        $this->participationSrv = $participationSrv;
        $this->apiSrv = $apiSrv;
        $this->offerSrv = $offerSrv;
        $this->guestPartiSrv = $guestPartiSrv;
        $this->paypalId = $paypalId;
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
    private function convertMealForDashboard(Meal $meal, ?Profile $profile): array
    {
        $description = null;
        $parentId = null;
        $participationId = null;
        $isOffering = false;
        $mealState = 'open';

        if (!$meal->getDish() instanceof DishVariation) {
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

        if (!$parentExistsInArray) {
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
        if ($meal->isLocked() && $meal->isOpen()) {
            $isOffering = $this->offerSrv->isOfferingMeal($profile, $meal);
            if ($isOffering) {
                return 'offering';
            } elseif (null !== $participant) {
                return 'offerable';
            } elseif ($this->offerSrv->getOfferCountByMeal($meal) > 0) {
                return 'tradeable';
            }
        }
        if (!$meal->isLocked() && $meal->isOpen() && !$meal->hasReachedParticipationLimit()) {
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
            if ($meal->getDish() instanceof DishVariation) {
                $this->addMealWithVariations($meal, null, $guestData['meals']);
            } else {
                $guestData['meals'][$meal->getId()] = $this->convertMealForDashboard($meal, null);
            }
        }

        return new JsonResponse($guestData, 200);
    }

    public function getPaypalId(): JsonResponse
    {
        return new JsonResponse($this->paypalId, 200);
    }
}