<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Event\MealOfferCancelledEvent;
use App\Mealz\MealBundle\Event\MealOfferedEvent;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Helper\ParticipationHelper;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepositoryInterface;
use App\Mealz\MealBundle\Service\Doorman;
use App\Mealz\MealBundle\Service\EventService;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\MealBundle\Service\ParticipationService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ParticipantController extends BaseController
{
    use ParticipantLoggingTrait;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EventService $eventSrv,
        private readonly ParticipationHelper $participationHelper,
        private readonly ParticipationService $participationSrv,
        private readonly MealRepositoryInterface $mealRepo,
        private readonly SlotRepositoryInterface $slotRepo,
        // dayRepo and participantRepo were only needed by admin actions, which have moved to KitchenStaffParticipantController
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
            $this->logger->info('join meal error', $this->getTrace($e));

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
            $this->logger->info('update combined meal error', $this->getTrace($e));

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

    /**
     * Determines wether the user is participating in a specific meal.
     * Returns null on error, -1 for not participating,
     * the id of the participation if they are participating.
     */
    public function isParticipating(Meal $meal): JsonResponse
    {
        $profile = $this->getProfile();
        if (null === $profile) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $participant = $meal->getParticipant($profile);
        if (null === $participant) {
            return new JsonResponse(-1, Response::HTTP_OK);
        }

        return new JsonResponse($participant->getId(), Response::HTTP_OK);
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
}
