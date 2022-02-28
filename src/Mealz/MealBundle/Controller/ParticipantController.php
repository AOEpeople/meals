<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DayRepository;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\Event\OfferUpdateEvent;
use App\Mealz\MealBundle\Event\SlotUpdateEvent;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Translator;

/**
 * @Security("is_granted('ROLE_USER')")
 */
class ParticipantController extends BaseController
{
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function updateCombinedMeal(Request $request, Participant $participant, ParticipationService $participationSrv): JsonResponse
    {
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

    public function delete(Participant $participant): JsonResponse
    {
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

        $this->eventDispatcher->dispatch(new ParticipationUpdateEvent($participant));
        if(!$this->getParticipantRepository()->hasParticipantBookedAMeal($meal->getDateTime(), $participant->getProfile())) {
            $this->eventDispatcher->dispatch(new SlotUpdateEvent($participant));
        }
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
            'participantsCount' => $meal->getParticipants()->count(),
            'url' => $this->generateUrl('MealzMealBundle_Meal_join', [
                'date' => $date,
                'dish' => $dish,
                'profile' => $profile,
            ]),
            'actionText' => 'deleted',
        ]);
    }

    /**
     * Offers an existing participation by setting the participant's 'offeredAt' value to the timestamp.
     * Takes an existing offer back by setting the 'offeredAt' value back to 0.
     */
    public function swap(Participant $participant, NotifierInterface $notifier): JsonResponse
    {
        $dateTime = $participant->getMeal()->getDateTime();
        $counter = $this->getParticipantRepository()->getOfferCount($dateTime);

        if (false === is_object($this->getUser())) {
            return $this->ajaxSessionExpiredRedirect();
        }

        if ($this->getProfile() !== $participant->getProfile() || false === $this->getDoorman()->isUserAllowedToSwap($participant->getMeal())) {
            return new JsonResponse(null, 403);
        }

        /*
         * Set "offeredAt" to the time.
         */
        if (0 === $participant->getOfferedAt()) {
            $participant->setOfferedAt(time());
        } else {
            // If user is already offering a meal (it's pending), take the offer back by setting "offeredAt" to 0.
            if (true === $participant->isPending()) {
                $participant->setOfferedAt(0);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->eventDispatcher->dispatch(new OfferUpdateEvent($participant));

            return $this->generateResponse('MealzMealBundle_Participant_swap', 'unswapped', $participant);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        $this->eventDispatcher->dispatch(new OfferUpdateEvent($participant));

        $dishTitle = $participant->getMeal()->getDish()->getTitleEn();

        if ($participant->getMeal()->getDish()->isCombinedDish()) {
            $combinedDishes = $participant->getCombinedDishes();
            /** @var Dish $dish */
            foreach ($combinedDishes as $dish) {
                $dishTitle = $dishTitle . ' - ' . $dish->getTitleEn();
            }
        }

        // If the meal has variations, get its parent and concatenate the title of the parent meal with the title of the variation.
        if ($participant->getMeal()->getDish()->getParent()) {
            $dishTitle = $participant->getMeal()->getDish()->getParent()->getTitleEn() . ' ' . $dishTitle;
        }

        // Mattermost integration
        $translator = new Translator('en_EN');
        $chefbotMessage = $translator->transChoice(
            $this->get('translator')->trans('mattermost.offered', [], 'messages'),
            $counter,
            [
                '%counter%' => $counter,
                '%dish%' => $dishTitle,
            ]
        );

        $notifier->sendAlert($chefbotMessage);

        return $this->generateResponse('MealzMealBundle_Participant_unswap', 'swapped', $participant);
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
}
