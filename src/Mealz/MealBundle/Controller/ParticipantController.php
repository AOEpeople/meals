<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\ParticipantRepository;
use Mealz\MealBundle\Service\Doorman;
use Mealz\UserBundle\Entity\Zombie;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Mealz\MealBundle\Entity\Meal;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ParticipantController extends Controller {

	/**
	 * @return Doorman
	 */
	protected function getDoorman() {
		return $this->get('mealz_meal.doorman');
	}

	public function joinAction(Meal $meal) {
		if(!$this->getUser() instanceof Zombie) {
			throw new AccessDeniedException();
		}
		if(!$this->getDoorman()->isUserAllowedToJoin($meal)) {
			throw new AccessDeniedException('You are not allowed to join this meal.');
		}

		try {
			$participant = new Participant();
			$participant->setUser($this->getUser());
			$participant->setMeal($meal);

			/** @var ParticipantRepository $participantRepository */
			$participantRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
			// that method ensures consistency by using a transaction
			$participantRepository->addParticipant($participant);

			$this->get('session')->getFlashBag()->add(
				'success',
				'You joined as participant to the meal.'
			);
		} catch (\InvalidArgumentException $e) {
			$this->get('session')->getFlashBag()->add(
				'info',
				'You are already joining this meal.'
			);
		}

		return $this->redirect($this->generateUrl('MealzMealBundle_Meal_show', array('meal' => $meal->getId())));
	}

	public function leaveAction(Meal $meal) {
		if(!$this->getUser() instanceof Zombie) {
			throw new AccessDeniedException();
		}
		if(!$this->getDoorman()->isUserAllowedToLeave($meal)) {
			throw new AccessDeniedException('You are not allowed to leave this meal.');
		}

		try {
			/** @var ParticipantRepository $participantRepository */
			$participantRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
			// that method ensures consistency by using a transaction
			$participantRepository->removeParticipantByUserAndMeal($this->getUser(), $meal);

			$this->get('session')->getFlashBag()->add(
				'success',
				'You were removed as participant to the meal.'
			);
		} catch (\InvalidArgumentException $e) {
			$this->get('session')->getFlashBag()->add(
				'info',
				'You have not joined the meal.'
			);
		}

		return $this->redirect($this->generateUrl('MealzMealBundle_Meal_show', array('meal' => $meal->getId())));
	}

}