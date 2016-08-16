<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use Mealz\MealBundle\Form\ParticipantForm;
use Mealz\UserBundle\Entity\Profile;
use Mealz\MealBundle\Entity\Meal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
}
