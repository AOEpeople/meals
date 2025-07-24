<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Entity\GuestInvitation;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Event\EventParticipationUpdateEvent;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Repository\GuestInvitationRepositoryInterface;
use App\Mealz\MealBundle\Service\EventParticipationService;
use App\Mealz\MealBundle\Service\GuestParticipationService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MealGuestController extends BaseController
{
    public function __construct(
        private readonly EventParticipationService $eventPartSrv,
        private readonly GuestParticipationService $guestPartSrv,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger
    ) {
    }

    public function joinAsGuest(Request $request): JsonResponse
    {
        try {
            [
                'profile' => $profile,
                'meals' => $meals,
                'slot' => $slot,
                'dishSlugs' => $dishSlugs,
            ] = $this->guestPartSrv->getGuestInvitationData($request);

            $participants = $this->guestPartSrv->join($profile, $meals, $slot, $dishSlugs);
            $this->triggerJoinEvents($participants);
        } catch (Exception $e) {
            $this->logger->info('guest join error', $this->getTrace($e));

            return new JsonResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * @param Day $mealDay meal day for which to generate the invitation
     */
    #[IsGranted('ROLE_USER')]
    public function newGuestInvitation(
        #[MapEntity(id: 'dayId')]
        Day $mealDay,
        GuestInvitationRepositoryInterface $guestInvitationRepo,
    ): JsonResponse {
        $userProfile = $this->getProfile();
        if (null === $userProfile) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $guestInvitation = $guestInvitationRepo->findOrCreateInvitation($userProfile, $mealDay);

        return new JsonResponse(['url' => $this->generateInvitationUrl($guestInvitation)], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER')]
    public function newGuestEventInvitation(
        Day $dayId,
        GuestInvitationRepositoryInterface $guestInvitationRepo,
        #[MapEntity(id: 'eventId')]
        EventParticipation $eventParticipation,
    ): JsonResponse {
        $userProfile = $this->getProfile();
        if (null === $userProfile) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $eventInvitation = $guestInvitationRepo->findOrCreateEventInvitation($userProfile, $dayId, $eventParticipation);

        return new JsonResponse(['url' => $this->generateInvitationUrl($eventInvitation, false)], Response::HTTP_OK);
    }

    public function getEventInvitationData(
        string $invitationId,
        GuestInvitationRepositoryInterface $guestInvitationRepo
    ): JsonResponse {
        /** @var GuestInvitation $invitation */
        $invitation = $guestInvitationRepo->find($invitationId);
        if (null === $invitation) {
            return new JsonResponse(['message' => '901: Could not find invitation for the given hash', 403]);
        }

        $guestData = [
            'date' => $invitation->getDay()->getDateTime(),
            'lockDate' => $invitation->getDay()->getLockParticipationDateTime(),
            'event' => $invitation->getEventParticipation() ? $invitation->getEventParticipation()->getEvent()->getTitle() : null,
        ];

        return new JsonResponse($guestData, Response::HTTP_OK);
    }

    public function joinEventAsGuest(
        string $invitationId,
        Request $request,
        GuestInvitationRepositoryInterface $guestInvitationRepo,
    ): JsonResponse {
        $parameters = json_decode($request->getContent(), true);

        /** @var GuestInvitation $invitation */
        $invitation = $guestInvitationRepo->find($invitationId);
        if (null === $invitation) {
            return new JsonResponse(['message' => '901: Could not find invitation for the given hash', 403]);
        } elseif (false === isset($parameters['firstName']) || false === isset($parameters['lastName'])) {
            return new JsonResponse(['message' => '902: Parameters were not provided'], Response::HTTP_NOT_FOUND);
        }

        if (false === isset($parameters['company'])) {
            $parameters['company'] = '';
        }

        try {
            $eventParticipation = $this->eventPartSrv->joinAsGuest(
                $parameters['firstName'],
                $parameters['lastName'],
                $parameters['company'],
                $invitation->getDay(),
                $invitation->getEventParticipation(),
            );

            $this->eventDispatcher->dispatch(new EventParticipationUpdateEvent($eventParticipation));

            return new JsonResponse(null, Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(['message' => '903: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Participant[] $participants
     */
    private function triggerJoinEvents(array $participants): void
    {
        if (false === isset($participants[0]) || false === ($participants[0] instanceof Participant)) {
            return;
        }

        // We trigger the event only once for one participant/meal.
        // Due to combined meal integration an update is sent for all the meals on the same day.
        $participant = $participants[0];
        $this->eventDispatcher->dispatch(new ParticipationUpdateEvent($participant));

        $slot = $participant->getSlot();
        if (null !== $slot) {
            $this->eventDispatcher->dispatch(new SlotAllocationUpdateEvent($participant->getMeal()->getDay(), $slot));
        }
    }

    private function generateInvitationUrl(GuestInvitation $invitation, bool $isMeal = true): string
    {
        return $this->generateUrl(
            true === $isMeal ? 'MealzMealBundle_Meal_guest' : 'MealzMealBundle_Meal_guest_event',
            ['hash' => $invitation->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
