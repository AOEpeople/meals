<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DayRepository;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Translator;

class ParticipantController extends BaseController
{
    public function delete(Participant $participant): JsonResponse
    {
        if (is_object($this->getUser()) === false) {
            return $this->ajaxSessionExpiredRedirect();
        }

        $meal = $participant->getMeal();
        if ($this->getDoorman()->isUserAllowedToLeave($meal) === false &&
            ($this->getProfile() === $participant->getProfile() || $this->getDoorman()->isKitchenStaff() === false)) {
            return new JsonResponse(null, 403);
        }

        $date = $meal->getDateTime()->format('Y-m-d');
        $dish = $meal->getDish()->getSlug();
        $profile = $participant->getProfile()->getUsername();

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($participant);
        $entityManager->flush();

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
        }

        $ajaxResponse = new JsonResponse();
        $ajaxResponse->setData(array(
            'participantsCount' => $meal->getParticipants()->count(),
            'url' => $this->generateUrl('MealzMealBundle_Meal_join', [
                'date' => $date,
                'dish' => $dish,
                'profile' => $profile,
            ]),
            'actionText' => 'deleted',
        ));

        return $ajaxResponse;
    }

    /**
     * Offers an existing participation by setting the participant's 'offeredAt' value to the timestamp.
     * Takes an existing offer back by setting the 'offeredAt' value back to 0.
     * @param Participant $participant
     * @param NotifierInterface $notifier
     * @return JsonResponse
     */
    public function swap(Participant $participant, NotifierInterface $notifier): JsonResponse
    {
        $dateTime = $participant->getMeal()->getDateTime();
        $counter = $this->getParticipantRepository()->getOfferCount($dateTime);

        if (is_object($this->getUser()) === false) {
            return $this->ajaxSessionExpiredRedirect();
        }

        if ($this->getProfile() !== $participant->getProfile() || $this->getDoorman()->isUserAllowedToSwap($participant->getMeal()) === false) {
            return new JsonResponse(null, 403);
        }

        if ($participant->getMeal() === null) {
            return new JsonResponse(null, 404);
        }

        /*
         * Set "offeredAt" to the time.
         */
        if ($participant->getOfferedAt() === 0) {
            $participant->setOfferedAt(time());
        } else {
            // If user is already offering a meal (it's pending), take the offer back by setting "offeredAt" to 0.
            if ($participant->isPending() === true) {
                $participant->setOfferedAt(0);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->generateResponse('MealzMealBundle_Participant_swap', 'unswapped', $participant);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        // If the meal has variations, get it's parent and concatenate the title of the parent meal with the title of the variation.
        $dishTitle = $participant->getMeal()->getDish()->getTitleEn();
        if ($participant->getMeal()->getDish()->getParent()) {
            $dishTitle = $participant->getMeal()->getDish()->getParent()->getTitleEn() . ' ' . $dishTitle;
        }

        // Mattermost integration
        $translator = new Translator('en_EN');
        $chefbotMessage = $translator->transChoice(
            $this->get('translator')->trans('mattermost.offered', array(), 'messages'),
            $counter,
            array(
                '%counter%' => $counter,
                '%dish%' => $dishTitle)
        );

        $notifier->sendAlert($chefbotMessage);

        return $this->generateResponse('MealzMealBundle_Participant_unswap', 'swapped', $participant);
    }

    /**
     * @param Participant $participant
     * @return JsonResponse
     * Checks if the participation of the current user is pending (being offered).
     */
    public function isParticipationPending(Participant $participant): JsonResponse
    {
        $ajaxResponse = new JsonResponse();
        $ajaxResponse->setData(
            $participant->isPending()
        );
        return $ajaxResponse;
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

    /**
     * @param Week $week
     * @return Response
     */
    public function editParticipation(Week $week): Response
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        /** @var WeekRepository $weekRepository */
        $weekRepository = $this->getDoctrine()->getRepository(Week::class);
        $week = $weekRepository->findWeekByDate($week->getStartTime(), array(
            'only_enabled_days' => true
        ));

        // Get user participation to list them as table rows
        $participantRepo = $this->getParticipantRepository();
        $participation = $participantRepo->getParticipantsOnDays(
            $week->getStartTime(),
            $week->getEndTime()
        );
        $groupedParticipation = $participantRepo->groupParticipantsByName($participation);

        /** @var Profile[] $profiles */
        $profiles = $this->getDoctrine()->getRepository(Profile::class)->findAll();
        $profilesArray = array();
        foreach ($profiles as $profile) {
            if (false === array_key_exists($profile->getUsername(), $groupedParticipation)) {
                $profilesArray[] = array(
                    'label' => $profile->getFullName(),
                    'value' => $profile->getUsername(),
                );
            }
        }

        // Create user participation row prototype
        $prototype = $this->renderView('@MealzMeal/Participant/edit_row_prototype.html.twig', array('week' => $week));

        return $this->render('MealzMealBundle:Participant:edit.html.twig', array(
            'week' => $week,
            'users' => $groupedParticipation,
            'profilesJson' => json_encode($profilesArray),
            'prototype' => $prototype,
        ));
    }

    private function generateResponse(string $route, string $action, Participant $participant): JsonResponse
    {
        $response = new JsonResponse();
        $response->setData(
            array(
                'url' => $this->generateUrl($route, array(
                    'participant' => $participant->getId(),
                )),
                'id' => $participant->getId(),
                'actionText' => $action,
            )
        );
        return $response;
    }
}
