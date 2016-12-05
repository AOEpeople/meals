<?php


namespace Mealz\MealBundle\Controller;

use Mealz\MealBundle\Entity\DayRepository;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\Week;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\HttpFoundation\JsonResponse;

class ParticipantController extends BaseController {
	public function deleteAction(Participant $participant) {
		if(!$this->getUser()) {
			return $this->ajaxSessionExpiredRedirect();
		}
		if($this->getProfile() !== $participant->getProfile() && !$this->getDoorman()->isKitchenStaff()) {
			return new JsonResponse(null, 403);
		}

		$meal = $participant->getMeal();
		if(!$this->getDoorman()->isUserAllowedToLeave($meal)) {
			return new JsonResponse(null, 403);
		}

		$date = $meal->getDateTime()->format('Y-m-d');
		$dish = $meal->getDish()->getSlug();
		$profile = $participant->getProfile()->getUsername();

		$em = $this->getDoctrine()->getManager();
		$em->remove($participant);
		$em->flush();

		if($this->getDoorman()->isKitchenStaff()) {
			$logger = $this->get('monolog.logger.balance');
			$logger->addInfo(
				'admin removed {profile} from {meal} (Meal: {mealId})',
				array(
					"profile" => $participant->getProfile(),
					"meal" => $meal,
					"mealId" => $meal->getId()
				)
			);
		}

		$ajaxResponse = new JsonResponse();
		$ajaxResponse->setData(array(
			'participantsCount' => $meal->getParticipants()->count(),
			'url' => $this->generateUrl('MealzMealBundle_Meal_join', array(
				'date' => $date,
				'dish' => $dish,
				'profile' => $profile
			)),
			'actionText' => $this->get('translator')->trans('deleted', array(), 'action')
		));

		return $ajaxResponse;
	}

    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        /** @var DayRepository $dayRepository */
        $dayRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Day');
        $day = $dayRepository->getCurrentDay();

        $participantRepository = $this->getParticipantRepository();
        $participations = $participantRepository->getParticipantsOnCurrentDay();

        /**
         * @TODO: get participants through week entity
         */
        $groupedParticipations = $participantRepository->groupParticipantsByName($participations);

        return $this->render('MealzMealBundle:Participant:list.html.twig', array(
            'day' => $day,
            'users' => $groupedParticipations
        ));
    }

    public function editParticipationAction(Week $week)
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        /** @var WeekRepository $weekRepository */
        $weekRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Week');
        $week = $weekRepository->findWeekByDate($week->getStartTime(), TRUE);

        $participantRepository = $this->getParticipantRepository();
        $participations = $participantRepository->getParticipantsOnDays(
            $week->getStartTime(),
            $week->getEndTime()
        );

        /**
         * @TODO: get participants through week entity
         */
        $groupedParticipations = $participantRepository->groupParticipantsByName($participations);

        /** @var Profile[] $profiles */
        $profiles = $this->getDoctrine()->getRepository('MealzUserBundle:Profile')->findAll();
        $profilesArray = array();
        foreach ($profiles as $profile) {
            if (FALSE === array_key_exists($profile->getUsername(), $groupedParticipations)) {
                $profilesArray[] = array(
                    'label' => $profile->getFullName(),
                    'value' => $profile->getUsername()
                );
            }
        }

        /**
         * @TODO: add select field for adding a new user
         */
        return $this->render('MealzMealBundle:Participant:edit.html.twig', array(
            'week' => $week,
            'users' => $groupedParticipations,
            'profilesJson' => json_encode($profilesArray)
        ));
    }
}
