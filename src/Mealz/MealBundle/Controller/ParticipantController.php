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
use App\Mealz\MealBundle\Helper\ParticipationHelper;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepositoryInterface;
use App\Mealz\MealBundle\Service\Doorman;
use App\Mealz\MealBundle\Service\EventService;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ParticipantController extends BaseController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EventService $eventSrv,
        private readonly ParticipationHelper $participationHelper,
        private readonly ParticipationService $participationSrv,
        private readonly MealRepositoryInterface $mealRepo,
        private readonly SlotRepositoryInterface $slotRepo,
        private readonly DayRepositoryInterface $dayRepo,
        private readonly ParticipantRepositoryInterface $participantRepo,
        private readonly LoggerInterface $logger,
        private readonly Doorman $doorman
    ) {
    }

    /**
     * Lets the currently logged-in user either join a meal, or accept an already booked meal offered by a participant.
     */
    public function joinMeal(Request $request): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
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
            $this->logger->error('join meal error', $this->getTrace($e));

            return new JsonResponse(['message' => '402: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (null === $result) {
            return new JsonResponse(['message' => '403: User is not allowed to join meal'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->eventSrv->triggerJoinEvents($result['participant'], $result['offerer']);

        if (null === $result['offerer']) {
            $this->logAdd($meal, $result['participant']);
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
                'mealState' => $this->participationHelper->getMealState($meal),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Lets the currently logged-in user either leave a meal, or put it up for offer.
     */
    public function leaveMeal(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }
        $parameters = json_decode($request->getContent(), true);
        $meal = $this->mealRepo->find($parameters['mealId']);
        $participant = $this->participationSrv->getParticipationByMealAndUser($meal, $profile);

        if (false === $this->doorman->isUserAllowedToLeave($meal)) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        } elseif (null === $participant) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $participant->setCombinedDishes(null);

        $entityManager->remove($participant);
        $entityManager->flush();

        $this->eventSrv->triggerLeaveEvents($participant);

        $this->logRemove($meal, $participant);

        $activeSlot = $this->participationSrv->getSlot($profile, $meal->getDateTime());
        $slotID = 0;
        if (null !== $activeSlot) {
            $slotID = $activeSlot->getId();
        }

        return new JsonResponse([
            'slotId' => $slotID,
            'mealState' => $this->participationHelper->getMealState($meal),
        ], Response::HTTP_OK);
    }

    public function updateCombinedMeal(
        Request $request,
        Participant $participant,
        ParticipationService $participationSrv
    ): JsonResponse {
        $dishSlugs = $request->request->get('dishes', null);

        try {
            $participationSrv->updateCombinedMeal($participant, $dishSlugs ?? []);
        } catch (ParticipationException $pex) {
            return new JsonResponse(['message' => $pex->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            $this->logger->error('update combined meal error', $this->getTrace($e));

            return new JsonResponse(['message' => 'unexpected error'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            Response::HTTP_OK
        );
    }

    /**
     * Makes a booked meal by a participant to be available for taken over.
     */
    public function offerMeal(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $participant = $this->getParticipantFromRequest($request);
        if (null === $participant) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }
        $meal = $participant->getMeal();

        if ($this->getProfile() !== $participant->getProfile()
            || false === $this->doorman->isUserAllowedToSwap($meal)) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $participant->setOfferedAt(time());

        $entityManager->persist($participant);
        $entityManager->flush();

        // trigger meal-offered event
        $this->eventDispatcher->dispatch(new MealOfferedEvent($participant));

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Cancels an offered meal by a participant, so it can no longer be taken over by other users.
     */
    public function cancelOfferedMeal(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        if (null === $parameters['mealId']) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $participant = $this->getParticipantFromRequest($request);
        if (null === $participant) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $participant->setOfferedAt(0);

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

    #[IsGranted('ROLE_KITCHEN_STAFF')]
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

        return new JsonResponse($response, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_KITCHEN_STAFF')]
    public function add(Profile $profile, Meal $meal, Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        try {
            if (true === $meal->isCombinedMeal() && false === isset($parameters['combiDishes'])) {
                throw new Exception('401: Combined Meals need exactly two dishes');
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
                $participationData[] = $this->participationHelper->getParticipationMealData($participation);
            }

            return new JsonResponse([
                'day' => $meal->getDay()->getId(),
                'profile' => $profile->getUsername(),
                'booked' => $participationData,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logger->error('error adding participant to meal', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[IsGranted('ROLE_KITCHEN_STAFF')]
    public function remove(EntityManagerInterface $entityManager, Profile $profile, Meal $meal): JsonResponse
    {
        try {
            $participation = $this->participationSrv->getParticipationByMealAndUser($meal, $profile);
            $participation->setCombinedDishes(null);

            $entityManager->remove($participation);
            $entityManager->flush();

            $this->eventSrv->triggerLeaveEvents($participation);
            $this->logRemove($meal, $participation);

            $participations = $this->participationSrv->getParticipationsByDayAndProfile($profile, $meal->getDay());

            $participationData = [];
            foreach ($participations as $participation) {
                $participationData[] = $this->participationHelper->getParticipationMealData($participation);
            }

            return new JsonResponse([
                'day' => $meal->getDay()->getId(),
                'profile' => $profile->getUsername(),
                'booked' => $participationData,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logger->error('error removing participant from meal', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[IsGranted('ROLE_KITCHEN_STAFF')]
    public function getProfilesWithoutParticipation(Week $week): JsonResponse
    {
        $participations = $this->participantRepo->getParticipantsOnDays($week->getStartTime(), $week->getEndTime());
        $response = $this->participationHelper->getNonParticipatingProfilesByWeek($participations);

        return new JsonResponse($response, Response::HTTP_OK);
    }

    /**
     * Returns the dishes for a combi meal of a participant.
     */
    public function getCombiForMeal(Meal $meal): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $participant = $meal->getParticipant($profile);
        if (null === $participant) {
            return new JsonResponse(['message' => 'No participation found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($participant->getCombinedDishes()->toArray(), Response::HTTP_OK);
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
        if (false === is_object($this->doorman->isKitchenStaff())) {
            return;
        }

        $this->logger->info(
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
        if (true === $this->doorman->isKitchenStaff()) {
            $this->logger->info(
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
        if (0 === count($participants)) {
            $response[$day->getId()] = new stdClass();

            return $response;
        }

        /** @var Participant $participant */
        foreach ($participants as $participant) {
            $participationData = $this->participationHelper->getParticipationMealData($participant);
            $response[$day->getId()][$participant->getProfile()->getFullName()]['booked'][] = $participationData;
            $response[$day->getId()][$participant->getProfile()->getFullName()]['profile'] = $participant->getProfile()->getUsername();
        }

        return $response;
    }
}
