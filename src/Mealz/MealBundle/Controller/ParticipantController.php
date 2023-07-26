<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Event\MealOfferCancelledEvent;
use App\Mealz\MealBundle\Event\MealOfferedEvent;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepositoryInterface;
use App\Mealz\MealBundle\Repository\WeekRepositoryInterface;
use App\Mealz\MealBundle\Service\EventService;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\MealBundle\Service\SlotService;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

/**
 * @Security("is_granted('ROLE_USER')")
 */
class ParticipantController extends BaseController
{
    private EventDispatcherInterface $eventDispatcher;
    private EventService $eventSrv;
    private SlotService $slotSrv;
    private ParticipationService $participationSrv;
    private MealRepositoryInterface $mealRepo;
    private SlotRepositoryInterface $slotRepo;
    private DayRepositoryInterface $dayRepo;
    private LoggerInterface $logger;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EventService $eventSrv,
        SlotService $slotSrv,
        ParticipationService $participationSrv,
        MealRepositoryInterface $mealRepo,
        SlotRepositoryInterface $slotRepo,
        DayRepositoryInterface $dayRepo,
        LoggerInterface $logger
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->eventSrv = $eventSrv;
        $this->slotSrv = $slotSrv;
        $this->participationSrv = $participationSrv;
        $this->mealRepo = $mealRepo;
        $this->slotRepo = $slotRepo;
        $this->dayRepo = $dayRepo;
        $this->logger = $logger;
    }

    /**
     * Lets the currently logged-in user either join a meal, or accept an already booked meal offered by a participant.
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function joinMeal(Request $request): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, 403);
        }

        $parameters = json_decode($request->getContent(), true);
        $slot = null;
        if (true === isset($parameters['slotID'])) {
            $slot = $this->slotRepo->find($parameters['slotID']);
        }
        $meal = $this->mealRepo->find($parameters['mealID']);

        try {
            $result = $this->participationSrv->join($profile, $meal, $slot, $parameters['dishSlugs']);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(null, 500);
        }

        $this->eventSrv->triggerJoinEvents($result['participant'], $result['offerer']);

        if (null === $result['offerer']) {
            $this->logAdd($meal, $result['participant']);
        }

        $mealState = 'open';
        if (true === $meal->isLocked() && true === $meal->isOpen()) {
            $mealState = 'offerable';
        }

        $slotID = 0;
        $slot = $result['slot'];
        if (null !== $slot) {
            $slotID = $slot->getId();
        }

        return new JsonResponse(
            [
                'slotId' => $slotID,
                'participantId' => $result['participant']->getId(),
                'mealState' => $mealState,
            ],
            200
        );
    }

    /**
     * Lets the currently logged-in user either leave a meal, or put it up for offer.
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function leaveMeal(Request $request): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, 403);
        }
        $parameters = json_decode($request->getContent(), true);
        $meal = $this->mealRepo->find($parameters['mealId']);
        $participant = $this->participationSrv->getParticipationByMealAndUser($meal, $profile);

        if (
            false === $this->getDoorman()->isUserAllowedToLeave($meal) &&
            false === $this->getDoorman()->isKitchenStaff()
        ) {
            return new JsonResponse(null, 403);
        }

        $participant->setCombinedDishes(null);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($participant);
        $entityManager->flush();

        $this->eventSrv->triggerLeaveEvents($participant);

        $this->logRemove($meal, $participant);

        $activeSlot = $this->participationSrv->getSlot($profile, $meal->getDateTime());
        $slotID = 0;
        if (null !== $activeSlot) {
            $slotID = $activeSlot->getId();
        }

        return new JsonResponse(['slotId' => $slotID], 200);
    }

    public function updateCombinedMeal(
        Request $request,
        Participant $participant,
        ParticipationService $participationSrv
    ): JsonResponse {
        $dishSlugs = $request->request->get('dishes', []);

        try {
            $participationSrv->updateCombinedMeal($participant, $dishSlugs);
        } catch (ParticipationException $pex) {
            return new JsonResponse(['error' => $pex->getMessage()], 422);
        } catch (Exception $exc) {
            $this->logException($exc);

            return new JsonResponse(['error' => 'unexpected error'], 500);
        }

        $this->eventDispatcher->dispatch(new ParticipationUpdateEvent($participant));

        return new JsonResponse(
            [
                'actionText' => 'updated',
                'bookedDishSlugs' => array_map(
                    static fn (Dish $dish) => $dish->getSlug(),
                    $participant->getCombinedDishes()->toArray()
                ),
            ],
            200
        );
    }

    /**
     * Makes a booked meal by a participant to be available for taken over.
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function offerMeal(Request $request): JsonResponse
    {
        if (false === is_object($this->getUser())) {
            return $this->ajaxSessionExpiredRedirect();
        }

        $participant = $this->getParticipantFromRequest($request);
        if (null === $participant) {
            return new JsonResponse(null, 403);
        }
        $meal = $participant->getMeal();

        if ($this->getProfile() !== $participant->getProfile()
            || false === $this->getDoorman()->isUserAllowedToSwap($meal)) {
            return new JsonResponse(null, 403);
        }

        $participant->setOfferedAt(time());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($participant);
        $entityManager->flush();

        // trigger meal-offered event
        $this->eventDispatcher->dispatch(new MealOfferedEvent($participant));

        return new JsonResponse(null, 200);
    }

    /**
     * Cancels an offered meal by a participant, so it can no longer be taken over by other users.
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function cancelOfferedMeal(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        if (null === $parameters['mealId']) {
            return new JsonResponse(null, 403);
        }

        $participant = $this->getParticipantFromRequest($request);
        if (null === $participant) {
            return new JsonResponse(null, 403);
        }

        $participant->setOfferedAt(0);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($participant);
        $entityManager->flush();

        $this->eventDispatcher->dispatch(new MealOfferCancelledEvent($participant));

        return $this->generateResponse('MealzMealBundle_Participant_swap', 'unswapped', $participant);
    }

    /**
     * Checks if the participation of the current user is pending (being offered).
     */
    public function isParticipationPending(Participant $participant): JsonResponse
    {
        return new JsonResponse([
            $participant->isPending(),
        ]);
    }

    public function editParticipation(
        Week $week,
        ParticipationService $participationSrv,
        ParticipantRepositoryInterface $participantRepo,
        WeekRepositoryInterface $weekRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        $filteredWeek = $weekRepo->findWeekByDate($week->getStartTime(), ['only_enabled_days' => true]);

        // If all days are disabled don't render list
        if (null === $filteredWeek) {
            $translator = $this->get('translator');
            $message = $translator->trans('error.all_days_disabled', [
                '%startDate%' => $week->getStartTime()->format('d.m'),
                '%endDate%' => $week->getEndTime()->format('d.m'),
            ]);
            $this->addFlashMessage($message, 'danger');

            return $this->redirectToRoute(
                'MealzMealBundle_Meal_edit',
                ['week' => $week->getId()]
            );
        }

        // Get user participation to list them as table rows
        $participation = $participantRepo->getParticipantsOnDays(
            $filteredWeek->getStartTime(),
            $filteredWeek->getEndTime(),
        );
        $groupedParticipation = $participantRepo->groupParticipantsByName($participation);

        /** @var Profile[] $profiles */
        $profiles = $this->getDoctrine()->getRepository(Profile::class)->findAll();
        $profilesArray = [];
        foreach ($profiles as $profile) {
            if (false === array_key_exists($profile->getUsername(), $groupedParticipation)) {
                $profilesArray[] = [
                    'label' => $profile->getFullName(),
                    'value' => $profile->getUsername(),
                ];
            }
        }

        // Create user participation row prototype
        $prototype = $this->renderView('@MealzMeal/Participant/edit_row_prototype.html.twig', ['week' => $week]);

        return $this->render('MealzMealBundle:Participant:edit.html.twig', [
            'participationSrv' => $participationSrv,
            'week' => $filteredWeek,
            'users' => $groupedParticipation,
            'profilesJson' => json_encode($profilesArray),
            'prototype' => $prototype,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function getParticipationsForWeek(Week $week): JsonResponse
    {
        $days = $week->getDays();

        $response = [];

        /** @var Day $day */
        foreach ($days as $day) {
            $meals = $day->getMeals();
            $participants = new ArrayCollection();
            /** @var Meal $meal */
            foreach ($meals as $meal) {
                $participants = new ArrayCollection(array_merge($participants->toArray(), $meal->getParticipants()->toArray()));
            }

            $response = $this->addParticipationInfo($response, $participants, $day);
        }

        return new JsonResponse($response, 200);
    }

    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function add(Profile $profile, Meal $meal, Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);
        $result = null;

        try {
            if (true === $meal->isCombinedMeal() && false === isset($parameters['combiDishes'])) {
                throw new Exception('Combined Meals need exactly two dishes');
            }

            if (true === isset($parameters['combiDishes'])) {
                $result = $this->participationSrv->join($profile, $meal, null, $parameters['combiDishes']);
            } else {
                $result = $this->participationSrv->join($profile, $meal);
            }

            $this->eventSrv->triggerJoinEvents($result['participant'], $result['offerer']);
            $this->logAdd($meal, $result['participant']);

            // get updated day
            $day = $this->dayRepo->getDayByDate($meal->getDay()->getDateTime());
            $participations = $this->participationSrv->getParticipationsByDayAndProfile($profile, $day);

            $participationData = [];
            foreach ($participations as $participation) {
                $this->logger->info('Participation for dataconversion: ' . $participation->getId());
                $participationData[] = $this->getParticipationData($participation);
            }

            return new JsonResponse([
                'day' => $meal->getDay()->getId(),
                'profile' => $profile->getFullName(),
                'booked' => $participationData
            ], 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function remove(Profile $profile, Meal $meal): JsonResponse
    {
        try {

            $participation = $this->participationSrv->getParticipationByMealAndUser($meal, $profile);
            $participation->setCombinedDishes(null);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($participation);
            $entityManager->flush();

            $this->eventSrv->triggerLeaveEvents($participation);
            $this->logRemove($meal, $participation);

            $participations = $this->participationSrv->getParticipationsByDayAndProfile($profile, $meal->getDay());

            $participationData = [];
            foreach ($participations as $participation) {
                $participationData[] = $this->getParticipationData($participation);
            }

            return new JsonResponse([
                'day' => $meal->getDay()->getId(),
                'profile' => $profile->getFullName(),
                'booked' => $participationData
            ], 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    private function generateResponse(string $route, string $action, Participant $participant): JsonResponse
    {
        return new JsonResponse([
            'url' => $this->generateUrl($route, ['participant' => $participant->getId()]),
            'id' => $participant->getId(),
            'actionText' => $action,
        ]);
    }

    private function getParticipantFromRequest(Request $request): ?Participant
    {
        $parameters = json_decode($request->getContent(), true);
        if (null === $parameters['mealId']) {
            return null;
        }
        $meal = $this->mealRepo->find($parameters['mealId']);

        return $this->participationSrv->getParticipationByMealAndUser($meal, $this->getProfile());
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

    private function logRemove(Meal $meal, Participant $participant): void
    {
        if (true === $this->getDoorman()->isKitchenStaff()) {
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
    }

    private function addParticipationInfo(array $response, ArrayCollection $participants, Day $day): array
    {
        /** @var Participant $participant */
        foreach ($participants as $participant) {
            $participationData = $this->getParticipationData($participant);
            $response[$day->getId()][$participant->getProfile()->getFullName()]['booked'][] = $participationData;
            $response[$day->getId()][$participant->getProfile()->getFullName()]['profile'] = $participant->getProfile()->getUsername();
        }

        return $response;
    }

    private function getParticipationData(Participant $participant): array
    {
        $participationData['mealId'] = $participant->getMeal()->getId();
        $participationData['dishId'] = $participant->getMeal()->getDish()->getId();
        $participationData['combinedDishes'] = array_map(
            fn ($dish) => $dish->getId(),
            $participant->getCombinedDishes()->toArray()
        );

        return $participationData;
    }
}
