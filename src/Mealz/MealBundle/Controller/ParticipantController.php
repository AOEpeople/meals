<?php

namespace Mealz\MealBundle\Controller;

use Mealz\MealBundle\Entity\DayRepository;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\Week;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\MealBundle\Service\Doorman;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;

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
        if ($this->getProfile() !== $participant->getProfile() && ($this->getDoorman()->isKitchenStaff()) === false) {
            return new JsonResponse(null, 403);
        }

        $meal = $participant->getMeal();
        if (!$this->getDoorman()->isUserAllowedToLeave($meal)) {
            return new JsonResponse(null, 403);
        }

        $date = $meal->getDateTime()->format('Y-m-d');
        $dish = $meal->getDish()->getSlug();
        $profile = $participant->getProfile()->getUsername();

        $em = $this->getDoctrine()->getManager();
        $em->remove($participant);
        $em->flush();

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
     * @param Participant $participant
     * @return JsonResponse
     */
    public function swapAction(Participant $participant)
    {
        $dateTime = $participant->getMeal()->getDateTime();
        $counter = $this->countMeals($dateTime);

        if (is_object($this->getUser()) === false) {
            return $this->ajaxSessionExpiredRedirect();
        }

        if ($this->getProfile() !== $participant->getProfile()) {
            return new JsonResponse(null, 403);
        }

        if (!$this->getDoorman()->isUserAllowedToSwap($participant->getMeal())) {
            return new JsonResponse(null, 403);
        }

        /* If the participant is already offering that meal, take the offer back, if the participant somehow tries to swap again.
        Otherwise just set "offeredAt" to the time. */
        if ($participant->getOfferedAt() === 0) {
            $participant->setOfferedAt(time());
        } else {
            $participant->setOfferedAt(0);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        /* If the meal has variations, get it's parent and concatenate the title of the parent meal with the title of the variation. */
        if ($participant->getMeal()->getDish()->getParent()) {
            $dish = $participant->getMeal()->getDish()->getParent()->getTitleEn() . " " . $participant->getMeal()->getDish()->getTitleEn();
        } else {
            $dish = $participant->getMeal()->getDish()->getTitleEn();
        }

        /* Mattermost integration */
        $translator = new Translator('en_EN');
        $translator->addLoader('array', new ArrayLoader());

        $chefbotMessage = $translator->transChoice('{0} One meal has just been offered for swapping: "%dish%". Log into your account at https://meals.aoe.com/ to get it!|
        {1} One meal has just been offered for swapping: "%dish%". %counter% other meal is currently offered. Log into your account at https://meals.aoe.com/ to get it!|
        [2, Inf[ One meal has just been offered for swapping: "%dish%". %counter% other meals are currently offered. Log into your account at https://meals.aoe.com/ to get it!',
            $counter, array(
                '%counter%' => $counter,
                '%dish%' => $dish)
        );

        //$this->slack($chefbotMessage);

        // Return JsonResponse
        $ajaxResponse = new JsonResponse();
        $ajaxResponse->setData(array(
            'url' => $this->generateUrl('MealzMealBundle_Participant_unswap', array(
                'participant' => $participant->getId(),
            )),
            'actionText' => $this->get('translator')->trans('offered', array(), 'action'),
        ));

        return $ajaxResponse;
    }

    /**
     * @param Participant $participant
     * @return JsonResponse
     */
    public function unswapAction(Participant $participant)
    {
        // If user is already offering a meal (it's pending), take the offer back by setting "offeredAt" to 0.
        if ($participant->isPending()) {
            $participant->setOfferedAt(0);
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        $ajaxResponse = new JsonResponse();
        $ajaxResponse->setData(array(
            'url' => $this->generateUrl('MealzMealBundle_Participant_swap', array(
                'participant' => $participant->getId(),
            )),
            'actionText' => $this->get('translator')->trans('unswapped', array(), 'action'),
        ));

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
        $participantRepository = $this->getParticipantRepository();
        $participation = $participantRepository->getParticipantsOnCurrentDay();
        $groupedParticipation = $participantRepository->groupParticipantsByName($participation);

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
        $week = $weekRepository->findWeekByDate($week->getStartTime(), true);

// Get user participation to list them as table rows
        $participantRepository = $this->getParticipantRepository();
        $participation = $participantRepository->getParticipantsOnDays(
            $week->getStartTime(),
            $week->getEndTime()
        );
        $groupedParticipation = $participantRepository->groupParticipantsByName($participation);

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
