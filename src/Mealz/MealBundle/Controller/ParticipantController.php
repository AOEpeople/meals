<?php

namespace Mealz\MealBundle\Controller;

use Mealz\MealBundle\Entity\DayRepository;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\Week;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\UserBundle\Entity\Profile;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\Translator;

/**
 * Class ParticipantController
 * @package Mealz\MealBundle\Controller
 */
class ParticipantController extends BaseController
{
    /**
     * delete participation
     * @param Participant $participant
     * @return JsonResponse
     */
    public function deleteAction(Participant $participant)
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
            $logger->addInfo(
                'admin removed {profile} from {meal} (Meal: {mealId})',
                array(
                    'profile' => $participant->getProfile(),
                    'meal' => $meal,
                    'mealId' => $meal->getId(),
                )
            );
        }

        $ajaxResponse = new JsonResponse();
        $ajaxResponse->setData(array(
            'participantsCount' => $meal->getParticipants()->count(),
            'url' => $this->generateUrl('MealzMealBundle_Meal_join', array(
                'date' => $date,
                'dish' => $dish,
                'profile' => $profile,
            )),
            'actionText' => $this->get('translator')->trans('deleted', array(), 'action'),
        ));

        return $ajaxResponse;
    }

    /**
     * Offers an existing participation by setting the participant's 'offeredAt' value to the timestamp.
     * Takes an existing offer back by setting the 'offeredAt' value back to 0.
     * @param Participant $participant
     * @return JsonResponse
     */
    public function swapAction(Participant $participant)
    {
        $dateTime = $participant->getMeal()->getDateTime();
        $counter = count($this->getParticipantRepository()->getPendingParticipants($dateTime));

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

            $ajaxResponse = new JsonResponse();
            $ajaxResponse->setData(array(
                'url' => $this->generateUrl('MealzMealBundle_Participant_swap', array(
                    'participant' => $participant->getId(),
                )),
                'actionText' => $this->get('translator')->trans('unswapped', array(), 'action'),
            ));

            return $ajaxResponse;
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

        $mattermostService = $this->container->get('mattermost.service');
        $mattermostService->sendMessage($chefbotMessage);

        // Return JsonResponse
        $ajaxResponse = new JsonResponse();
        $ajaxResponse->setData(array(
            'url' => $this->generateUrl('MealzMealBundle_Participant_unswap', array(
                'participant' => $participant->getId(),
            )),
            'id' => $participant->getId(),
            'actionText' => $this->get('translator')->trans('swapped', array(), 'action'),
        ));

        return $ajaxResponse;
    }

    /**
     * @param Participant $participant
     * @return JsonResponse
     * Checks if the participation of the current user is pending (being offered).
     */
    public function isParticipationPendingAction(Participant $participant)
    {
        $ajaxResponse = new JsonResponse();
        $ajaxResponse->setData(
            $participant->isPending()
        );
        return $ajaxResponse;
    }

    /**
     * list participation
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        /** @var DayRepository $dayRepository */
        $dayRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Day');
        $day = $dayRepository->getCurrentDay();

        // Get user participation to list them as table rows
        $participantRepo = $this->getParticipantRepository();
        $participation = $participantRepo->getParticipantsOnCurrentDay();
        $groupedParticipation = $participantRepo->groupParticipantsByName($participation);

        return $this->render('MealzMealBundle:Participant:list.html.twig', array(
            'day' => $day,
            'users' => $groupedParticipation,
        ));
    }

    /**
     * edit participation
     * @param Week $week
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editParticipationAction(Week $week)
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        /** @var WeekRepository $weekRepository */
        $weekRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Week');
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
        $profiles = $this->getDoctrine()->getRepository('MealzUserBundle:Profile')->findAll();
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
}
