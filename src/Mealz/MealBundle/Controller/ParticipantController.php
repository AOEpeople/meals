<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Event\MealOfferCancelledEvent;
use App\Mealz\MealBundle\Event\MealOfferedEvent;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Repository\DayRepository;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Repository\WeekRepositoryInterface;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\MealBundle\Service\MealAvailabilityService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Exception;
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

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
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

    public function delete(
        MealAvailabilityService $availabilityService,
        Participant $participant,
        ParticipationService $participationSrv
    ): JsonResponse {
        if (false === is_object($this->getUser())) {
            return $this->ajaxSessionExpiredRedirect();
        }

        $meal = $participant->getMeal();
        if (false === $this->getDoorman()->isUserAllowedToLeave($meal) &&
            ($this->getProfile() === $participant->getProfile() || false === $this->getDoorman()->isKitchenStaff())) {
            return new JsonResponse(null, 403);
        }

        $date = $meal->getDateTime()->format('Y-m-d');
        $dish = $meal->getDish()->getSlug();
        $profile = $participant->getProfile()->getUsername();
        $participant->setCombinedDishes(null);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($participant);
        $entityManager->flush();

        $this->triggerDeleteEvents($participant);

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
        } else {
            $profile = null;    // we don't need to send any identity info for already logged-in user
        }

        return new JsonResponse([
            'participantsCount' => $participationSrv->getCountByMeal($meal),
            'url' => $this->generateUrl('MealzMealBundle_Meal_join', [
                'date' => $date,
                'dish' => $dish,
                'profile' => $profile,
            ]),
            'actionText' => 'deleted',
            'available' => $availabilityService->isAvailable($meal),
        ]);
    }

    /**
     * Makes a booked meal by a participant to be available for taken over.
     */
    public function offerMeal(Participant $participant): JsonResponse
    {
        if (false === is_object($this->getUser())) {
            return $this->ajaxSessionExpiredRedirect();
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

        return $this->generateResponse('MealzMealBundle_Participant_unswap', 'swapped', $participant);
    }

    /**
     * Cancels an offered meal by a participant, so it can no longer be taken over by other users.
     */
    public function cancelOfferedMeal(Participant $participant): JsonResponse
    {
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

        $translator = $this->get('translator');
        $filteredWeek = $weekRepo->findWeekByDate($week->getStartTime(), ['only_enabled_days' => true]);

        // If all days are disabled don't render list
        if (null === $filteredWeek) {
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
                $label = $profile->getFullName();
                if (true === $profile->isGuest()) {
                    $label .= ' (' . $translator->trans('profile.guest') . ')';
                }
                $profilesArray[] = [
                    'label' => $label,
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

    private function generateResponse(string $route, string $action, Participant $participant): JsonResponse
    {
        return new JsonResponse([
            'url' => $this->generateUrl($route, ['participant' => $participant->getId()]),
            'id' => $participant->getId(),
            'actionText' => $action,
        ]);
    }

    private function triggerDeleteEvents(Participant $participant): void
    {
        $this->eventDispatcher->dispatch(new ParticipationUpdateEvent($participant));

        $slot = $participant->getSlot();
        if (null !== $slot) {
            $this->eventDispatcher->dispatch(
                new SlotAllocationUpdateEvent($participant->getMeal()->getDateTime(), $slot)
            );
        }
    }
}
