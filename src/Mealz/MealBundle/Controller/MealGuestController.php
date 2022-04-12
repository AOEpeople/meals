<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\GuestInvitation;
use App\Mealz\MealBundle\Entity\GuestInvitationRepository;
use App\Mealz\MealBundle\Entity\InvitationWrapper;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Form\Guest\InvitationForm;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\MealBundle\Service\GuestParticipationService;
use App\Mealz\MealBundle\Service\MealAvailabilityService;
use App\Mealz\MealBundle\Service\ParticipationCountService;
use App\Mealz\UserBundle\Entity\Profile;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MealGuestController extends BaseController
{
    /**
     * @ParamConverter("invitation", options={"id" = "hash"})
     */
    public function joinAsGuest(
        Request $request,
        EventDispatcherInterface $eventDispatcher,
        GuestInvitation $invitation,
        GuestParticipationService $gps,
        MealAvailabilityService $availabilityService
    ): Response {
        $form = $this->getGuestInvitationForm($invitation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                ['profile' => $profile, 'meals' => $meals, 'slot' => $slot] = $this->getGuestInvitationData($form);

                $dishSlugs = $request->request->get('dishes', []);

                $participants = $gps->join($profile, $meals, $slot, $dishSlugs);
                $this->triggerJoinEvents($eventDispatcher, $participants);

                $message = $this->get('translator')->trans('participation.successful', [], 'messages');
                $this->addFlashMessage($message, 'success');

                return $this->render('base.html.twig');
            } catch (ParticipationException $pex) {
                $this->addFlashMessage($this->exceptionToError($pex), 'danger');
            } catch (Exception $e) {
                $this->logException($e, 'guest registration error');
                $this->addFlashMessage($this->exceptionToError($e), 'danger');
            }
        }

        return $this->render('MealzMealBundle:Meal:guest.html.twig', [
            'form' => $form->createView(),
            'participation' => ParticipationCountService::getParticipationByDay($invitation->getDay()),
            'availabilityService' => $availabilityService,
        ]);
    }

    private function getGuestInvitationForm(GuestInvitation $invitation): FormInterface
    {
        $invitationWrapper = new InvitationWrapper();
        $invitationWrapper->setDay($invitation->getDay());
        $invitationWrapper->setProfile(new Profile());

        return $this->createForm(InvitationForm::class, $invitationWrapper);
    }

    /**
     * @throws ParticipationException
     */
    private function getGuestInvitationData(FormInterface $form): array
    {
        $data = [
            'profile' => $form->get('profile')->getData(),
            'meals' => $form->get('day')->get('meals')->getData(),
            'slot' => $form->get('slot')->getData(),
        ];

        if ((null === $data['meals']) || (0 === count($data['meals']))) {
            throw new ParticipationException('invalid data', ParticipationException::ERR_GUEST_REG_MEAL_NOT_FOUND);
        }

        return $data;
    }

    private function exceptionToError(Exception $exception): string
    {
        $translator = $this->get('translator');

        if ($exception instanceof ParticipationException) {
            switch ($exception->getCode()) {
                case ParticipationException::ERR_GUEST_REG_MEAL_NOT_FOUND:
                    return $translator->trans('error.participation.no_meal_selected', [], 'messages');
                case ParticipationException::ERR_MEAL_NOT_BOOKABLE:
                    /** @var Meal $unbookableMeal */
                    $unbookableMeal = $exception->getContext()['meal'];

                    return $translator->trans(
                        'error.meal.join_not_allowed',
                        ['%dish%' => $unbookableMeal->getDish()->getTitle()],
                        'messages'
                    );
            }
        }

        return $translator->trans('error.unknown', [], 'messages');
    }

    /**
     * @param Day $mealDay meal day for which to generate the invitation
     * @ParamConverter("mealDay", options={"mapping": {"dayId": "id"}})
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function newGuestInvitation(Day $mealDay): JsonResponse
    {
        /** @var GuestInvitationRepository $guestInvitationRepo */
        $guestInvitationRepo = $this->getDoctrine()->getRepository(GuestInvitation::class);
        $guestInvitation = $guestInvitationRepo->findOrCreateInvitation($this->getUser()->getProfile(), $mealDay);

        return new JsonResponse(
            $this->generateUrl(
                'MealzMealBundle_Meal_guest',
                ['hash' => $guestInvitation->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            200
        );
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param Participant[]            $participants
     */
    private function triggerJoinEvents(EventDispatcherInterface $eventDispatcher, array $participants): void {
        if (!isset($participants[0]) || !($participants[0] instanceof Participant)) {
            return;
        }

        // We trigger the event only once for one participant/meal.
        // Due to combined meal integration an update is sent for all the meals on the same day.
        $participant = $participants[0];
        $eventDispatcher->dispatch(new ParticipationUpdateEvent($participant));

        $slot = $participant->getSlot();
        if (null !== $slot) {
            $eventDispatcher->dispatch(new SlotAllocationUpdateEvent($participant->getMeal()->getDateTime(), $slot));
        }
    }
}
