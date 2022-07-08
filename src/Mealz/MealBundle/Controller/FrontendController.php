<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Repository\MealRepository;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Repository\SlotRepository;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Event\MealOfferAcceptedEvent;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Service\DishService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\MealBundle\Service\SlotService;
use App\Mealz\MealBundle\Service\WeekService;
use App\Mealz\UserBundle\Entity\Profile;
use Exception;
use JMS\Serializer\Tests\Serializer\EventDispatcher\EventDispatcherTest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FrontendController extends BaseController
{
    private DishService $dishSrv;
    private SlotService $slotSrv;
    private WeekService $weekSrv;
    private ParticipationService $participationSrv;

    private SlotRepository $slotRepo;
    private MealRepository $mealRepo;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        DishService $dishSrv,
        SlotService $slotSrv,
        SlotRepository $slotRepo,
        WeekService $weekSrv,
        ParticipationService $participationSrv,
        MealRepository $mealRepo,
        EventDispatcherInterface $eventDispatcher
    ){
        $this->dishSrv = $dishSrv;
        $this->slotSrv = $slotSrv;
        $this->slotRepo = $slotRepo;
        $this->weekSrv = $weekSrv;
        $this->participationSrv = $participationSrv;
        $this->mealRepo = $mealRepo;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function renderIndex(): Response
    {
        return $this->render('MealzMealBundle:Test:index.html.twig');
    }

    /**
     * Lets the currently logged-in user either join a meal, or accept an already booked meal offered by a participant.
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function joinMeal(Request $request): JsonResponse
    {
        $profile = $this->getUser()->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, 403);
        }

        $parameters = json_decode($request->getContent(), true);
        $slot = $this->slotRepo->find($parameters['slotID']);
        $meal = $this->mealRepo->find($parameters['mealID']);

        try {
            $result = $this->participationSrv->join($profile, $meal, $slot, $parameters['dishSlugs']);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(null, 500);
        }

        $this->triggerJoinEvents($result['participant'], $result['offerer']);

        if (null === $result['offerer']) {
            $this->logAdd($meal, $result['participant']);
        }

        return new JsonResponse($result['participant']->getId());
    }

    /**
     * Lets the currently logged-in user either leave a meal, or put it up for offer
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function leaveMeal(Request $request): JsonResponse {
        if (false === is_object($this->getUser())) {
            return $this->ajaxSessionExpiredRedirect();
        }

        $parameters = json_decode($request->getContent(), true);
        $meal = $this->mealRepo->find($parameters['mealID']);
        $participant = $this->participationSrv->getParticipationByMealAndUser($meal);

        if (false === $this->getDoorman()->isUserAllowedToLeave($meal) &&
            (false === $this->getDoorman()->isKitchenStaff())) {
            return new JsonResponse(null, 403);
        }

        $participant->setCombinedDishes(null);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($participant);
        $entityManager->flush();

        $this->triggerLeaveEvents($participant);

        if (($this->getDoorman()->isKitchenStaff()) === true) {
            $logger = $this->get('monolog.logger.balance');
            $logger->info(
                'admin removed {profile} from {meal} (Meal: {mealId})',
                [
                    'profile' => $participant->getProfile(),
                    'meal' => $meal,
                    'mealId' => $meal->getId(),
                ]
            );
        }

        return new JsonResponse(null, 200);
    }

    /**
     * @throws Exception
     */
    public function ApiGetDashboardData(): JsonResponse
    {
        $weeks = $this->weekSrv->getNextTwoWeeks();

        $response = [];
        /* @var Week $week */
        foreach ($weeks as $week) {

            $days = [];
            /* @var Day $day */
            foreach ($week->getDays() as $day) {

                $activeSlot = $this->participationSrv->getSlot($this->getProfile(), $day->getDateTime());
                if ($activeSlot) {
                    $activeSlot = $activeSlot->getId();
                }
                $slots = $this->slotSrv->getSlotStatusForDay($day->getDateTime());
                array_unshift($slots, ["id" => -1, "title" => "auto", "count" => 0, "limit" => 0, "slug" => "auto"]);

                $meals = [];
                /* @var Meal $meal */
                foreach ($day->getMeals() as $meal) {

                    if ($meal->getDish() instanceof DishVariation) {
                        $parentExistsInArray = false;

                        /* @var Meal $addedMeal */
                        foreach ($meals as $id => $addedMeal) {
                            if ($addedMeal['id'] === $meal->getDish()->getParent()->getId()) {

                                $meals[$id]['variations'][] = [
                                    'id' => $meal->getId(),
                                    'title' => [
                                        'en' => $meal->getDish()->getTitleEn(),
                                        'de' => $meal->getDish()->getTitleDe()
                                    ],
                                    'dishSlug' => $meal->getDish()->getSlug(),
                                    'price' => $meal->getPrice(),
                                    'limit' => $meal->getParticipationLimit(),
                                    'reachedLimit' => $meal->hasReachedParticipationLimit(),
                                    'isOpen' => $meal->isOpen(),
                                    'isLocked' => $meal->isLocked(),
                                    'isNew' => $this->dishSrv->isNew($meal->getDish()),
                                    'participations' => $meal->getParticipants()->count(),
                                    'isParticipating' => $this->participationSrv->isUserParticipating($meal)
                                ];
                                $parentExistsInArray = true;
                                break;
                            }
                        }
                        if (!$parentExistsInArray) {
                            $meals[] = [
                                'id' => $meal->getDish()->getParent()->getId(),
                                'title' => [
                                    'en' => $meal->getDish()->getParent()->getTitleEn(),
                                    'de' => $meal->getDish()->getParent()->getTitleDe()
                                ],
                                'variations' => array([
                                    'id' => $meal->getId(),
                                    'title' => [
                                        'en' => $meal->getDish()->getTitleEn(),
                                        'de' => $meal->getDish()->getTitleDe()
                                    ],
                                    'dishSlug' => $meal->getDish()->getSlug(),
                                    'price' => $meal->getPrice(),
                                    'limit' => $meal->getParticipationLimit(),
                                    'reachedLimit' => $meal->hasReachedParticipationLimit(),
                                    'isOpen' => $meal->isOpen(),
                                    'isLocked' => $meal->isLocked(),
                                    'isNew' => $this->dishSrv->isNew($meal->getDish()),
                                    'participations' => $meal->getParticipants()->count(),
                                    'isParticipating' => $this->participationSrv->isUserParticipating($meal)
                                ])
                            ];
                        }
                    } else {
                        $meals[] = [
                            'id' => $meal->getId(),
                            'title' => [
                                'en' => $meal->getDish()->getTitleEn(),
                                'de' => $meal->getDish()->getTitleDe()
                            ],
                            'description' => [
                                'en' => $meal->getDish()->getDescriptionEn(),
                                'de' => $meal->getDish()->getDescriptionDe()
                            ],
                            'dishSlug' => $meal->getDish()->getSlug(),
                            'price' => $meal->getPrice(),
                            'limit' => $meal->getParticipationLimit(),
                            'reachedLimit' => $meal->hasReachedParticipationLimit(),
                            'isOpen' => $meal->isOpen(),
                            'isLocked' => $meal->isLocked(),
                            'isNew' => $this->dishSrv->isNew($meal->getDish()),
                            'participations' => $meal->getParticipants()->count(),
                            'isParticipating' => $this->participationSrv->isUserParticipating($meal)
                        ];
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

    private function triggerJoinEvents(Participant $participant, ?Profile $offerer): void
    {
        if (null !== $offerer) {
            $this->eventDispatcher->dispatch(new MealOfferAcceptedEvent($participant, $offerer));

            return;
        }

        $this->eventDispatcher->dispatch(new ParticipationUpdateEvent($participant));

        $slot = $participant->getSlot();
        if (null !== $slot) {
            $this->eventDispatcher->dispatch(new SlotAllocationUpdateEvent($participant->getMeal()->getDateTime(), $slot));
        }
    }

    private function triggerLeaveEvents(Participant $participant): void
    {
        $this->eventDispatcher->dispatch(new ParticipationUpdateEvent($participant));

        $slot = $participant->getSlot();
        if (null !== $slot) {
            $this->eventDispatcher->dispatch(
                new SlotAllocationUpdateEvent($participant->getMeal()->getDateTime(), $slot)
            );
        }
    }

    /**
     * Log add action of staff member.
     */
    private function logAdd(Meal $meal, Participant $participant): void
    {
        if (false === is_object($this->getDoorman()->isKitchenStaff())) {
            return;
        }

        $logger = $this->get('monolog.logger.balance');
        $logger->info(
            'admin added {profile} to {meal} (Participant: {participantId})',
            [
                'participantId' => $participant->getId(),
                'profile' => $participant->getProfile(),
                'meal' => $meal,
            ]
        );
    }
}
