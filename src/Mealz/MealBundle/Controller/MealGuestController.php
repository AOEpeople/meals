<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\GuestInvitation;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Repository\GuestInvitationRepositoryInterface;
use App\Mealz\MealBundle\Service\GuestParticipationService;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MealGuestController extends BaseController
{
    private GuestParticipationService $gps;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(GuestParticipationService $gps, EventDispatcherInterface $eventDispatcher)
    {
        $this->gps = $gps;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function joinAsGuest(Request $request): JsonResponse
    {
        try {
            [
                'profile' => $profile,
                'meals' => $meals,
                'slot' => $slot,
                'dishSlugs' => $dishSlugs
            ] = $this->gps->getGuestInvitationData($request);

            $participants = $this->gps->join($profile, $meals, $slot, $dishSlugs);
            $this->triggerJoinEvents($participants);
        } catch (Exception $e) {
            $this->logException($e, 'guest registration error');

            return new JsonResponse($e->getMessage(), 400);
        }

        return new JsonResponse(null, 200);
    }

    /**
     * @param Day $mealDay meal day for which to generate the invitation
     *
     * @ParamConverter("mealDay", options={"mapping": {"dayId": "id"}})
     * @Security("is_granted('ROLE_USER')")
     */
    public function newGuestInvitation(
        Day $mealDay,
        GuestInvitationRepositoryInterface $guestInvitationRepo
    ): JsonResponse {
        $guestInvitation = $guestInvitationRepo->findOrCreateInvitation($this->getUser()->getProfile(), $mealDay);

        return new JsonResponse(['url' => $this->generateInvitationUrl($guestInvitation),], 200);
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     */
    public function newGuestEventInvitation(
        Day $dayId,
        GuestInvitationRepositoryInterface $guestInvitationRepo
    ): JsonResponse {
        $eventInvitation = $guestInvitationRepo->findOrCreateInvitation($this->getUser()->getProfile(), $dayId);

        return new JsonResponse(['url' => $this->generateInvitationUrl($eventInvitation, false),], 200);
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
            'event' => $invitation->getDay()->getEvent()->getEvent()->getTitle(),
        ];

        return new JsonResponse($guestData, 200);
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
