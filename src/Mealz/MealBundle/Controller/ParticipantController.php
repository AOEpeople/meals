<?php

namespace Mealz\MealBundle\Controller;

use Mealz\MealBundle\Entity\DayRepository;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\Week;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        if (!$this->getUser()) {
            return $this->ajaxSessionExpiredRedirect();
        }
        if ($this->getProfile() !== $participant->getProfile() && !$this->getDoorman()->isKitchenStaff()) {
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

        if ($this->getDoorman()->isKitchenStaff()) {
            $logger = $this->get('monolog.logger.balance');
            $logger->addInfo(
                'admin removed {profile} from {meal} (Meal: {mealId})',
                array(
                    "profile" => $participant->getProfile(),
                    "meal" => $meal,
                    "mealId" => $meal->getId(),
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
