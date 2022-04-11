<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DayRepository;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\Event\MealOfferedEvent;
use App\Mealz\MealBundle\Event\MealOfferCancelledEvent;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\MealBundle\Service\MealAvailabilityService;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
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
    public function offerMeal(Participant $participant, NotifierInterface $notifier): JsonResponse
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
        $this->eventDispatcher->dispatch(new MealOfferedEvent($meal));

        // Mattermost integration
        $dishTitle = $this->getBookedDishTitle($participant);
        $counter = $this->getParticipantRepository()->getOfferCount($meal->getDateTime());

        $chefBotMessage = $this->get('translator')->trans(
            'mattermost.offered',
            [
                '%counter%' => $counter,
                '%dish%' => $dishTitle,
            ],
            'messages',
            'en_EN'
        );

        $notifier->sendAlert($chefBotMessage);

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
    public function list(DayRepository $dayRepo): Response
    {
        $participants = [];
        $day = $dayRepo->getCurrentDay();

        if (null === $day) {
            $day = new Day();
            $day->setDateTime(new DateTime());
        } else {
            $participantRepo = $this->getParticipantRepository();
            $participants = $participantRepo->findAllGroupedBySlotAndProfileID($day->getDateTime());
        }

        return $this->render('MealzMealBundle:Participant:list.html.twig', [
            'day' => $day,
            'users' => $participants,
        ]);
    }

    public function editParticipation(Week $week): Response
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        /** @var WeekRepository $weekRepository */
        $weekRepository = $this->getDoctrine()->getRepository(Week::class);
        $week = $weekRepository->findWeekByDate($week->getStartTime(), ['only_enabled_days' => true]);

        // Get user participation to list them as table rows
        $participantRepo = $this->getParticipantRepository();
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

    private function getBookedDishTitle(Participant $participant): string
    {
        $bookedDish = $participant->getMeal()->getDish();
        $dishTitle = $bookedDish->getTitleEn();

        if ($bookedDish->isCombinedDish()) {
            $combinedDishes = $participant->getCombinedDishes();
            /** @var Dish $dish */
            foreach ($combinedDishes as $dish) {
                $dishTitle .= ' - ' . $dish->getTitleEn();
            }
        }

        // If the meal has variations, get its parent and concatenate
        // the title of the parent meal with the title of the variation.
        if ($bookedDish->getParent()) {
            $dishTitle = $bookedDish->getParent()->getTitleEn() . ' ' . $dishTitle;
        }

        return $dishTitle;
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
