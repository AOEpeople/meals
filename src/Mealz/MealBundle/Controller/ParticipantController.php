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
use App\Mealz\MealBundle\Repository\DayRepository;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepositoryInterface;
use App\Mealz\MealBundle\Repository\WeekRepositoryInterface;
use App\Mealz\MealBundle\Service\EventService;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Exception;
use PHPUnit\Util\Json;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_USER')")
 */
class ParticipantController extends BaseController
{
    private EventDispatcherInterface $eventDispatcher;
    private EventService $eventSrv;
    private ParticipationService $participationSrv;
    private MealRepositoryInterface $mealRepo;
    private SlotRepositoryInterface $slotRepo;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EventService $eventSrv,
        ParticipationService $participationSrv,
        MealRepositoryInterface $mealRepo,
        SlotRepositoryInterface $slotRepo
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->eventSrv = $eventSrv;
        $this->participationSrv = $participationSrv;
        $this->mealRepo = $mealRepo;
        $this->slotRepo = $slotRepo;
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

        $slot = null;

        $parameters = json_decode($request->getContent(), true);
        if (0 !== $parameters['slotID']) {
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

        return new JsonResponse(['slotID' => $result['slot']->getId()]);
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
        $meal = $this->mealRepo->find($parameters['mealID']);
        $participant = $this->participationSrv->getParticipationByMealAndUser($meal, $profile);

        if (false === $this->getDoorman()->isUserAllowedToLeave($meal) &&
            (false === $this->getDoorman()->isKitchenStaff())) {
            return new JsonResponse(null, 403);
        }

        $participant->setCombinedDishes(null);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($participant);
        $entityManager->flush();

        $this->eventSrv->triggerLeaveEvents($participant);

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

        $activeSlot = $this->participationSrv->getSlot($profile, $meal->getDateTime());
        $slotID = 0;
        if (null !== $activeSlot) {
            $slotID = $activeSlot->getId();
        }

        return new JsonResponse(['slotID' => $slotID], 200);
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

        $this->eventDispatcher->dispatch(new MealOfferCancelledEvent($participant->getMeal()));

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

    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function list(DayRepository $dayRepo, ParticipantRepositoryInterface $participantRepo): Response
    {
        $participants = [];
        $day = $dayRepo->getCurrentDay();

        if (null === $day) {
            $day = new Day();
            $day->setDateTime(new DateTime());
        } else {
            $participants = $participantRepo->findAllGroupedBySlotAndProfileID($day->getDateTime());
        }

        return $this->render('MealzMealBundle:Participant:list.html.twig', [
            'day' => $day,
            'users' => $participants,
        ]);
    }

    public function editParticipation(
        Week $week,
        ParticipationService $participationSrv,
        ParticipantRepositoryInterface $participantRepo,
        WeekRepositoryInterface $weekRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        $week = $weekRepo->findWeekByDate($week->getStartTime(), ['only_enabled_days' => true]);

        // Get user participation to list them as table rows
        $participation = $participantRepo->getParticipantsOnDays(
            $week->getStartTime(),
            $week->getEndTime()
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
            'week' => $week,
            'users' => $groupedParticipation,
            'profilesJson' => json_encode($profilesArray),
            'prototype' => $prototype,
        ]);
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
}
