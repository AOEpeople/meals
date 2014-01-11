<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\ParticipantRepository;
use Mealz\MealBundle\Service\Doorman;
use Mealz\UserBundle\Entity\Zombie;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Mealz\MealBundle\Entity\Meal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ParticipantController extends Controller {

	/**
	 * @return Doorman
	 */
	protected function getDoorman() {
		return $this->get('mealz_meal.doorman');
	}

	/**
	 * @return ParticipantRepository
	 */
	protected function getParticipantRepository() {
		return $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
	}

	/**
	 * let the currently logged in user join the given meal
	 *
	 * @param Meal $meal
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
	 */
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

					// that method ensures consistency by using a transaction
			$this->getParticipantRepository()->addParticipant($participant);

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

	/**
	 * let the currently logged in user leave the meal
	 *
	 * @param Meal $meal
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
	 */
	public function leaveAction(Meal $meal) {
		if(!$this->getUser() instanceof Zombie) {
			throw new AccessDeniedException();
		}
		if(!$this->getDoorman()->isUserAllowedToLeave($meal)) {
			throw new AccessDeniedException('You are not allowed to leave this meal.');
		}

		try {
			// that method ensures consistency by using a transaction
			$this->getParticipantRepository()->removeParticipantByUserAndMeal($this->getUser(), $meal);

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

	public function commentAction(Meal $meal, Request $request) {
		if(!$this->getUser() instanceof Zombie) {
			throw new AccessDeniedException();
		}

		$form = $this->getCommentForm($this->getUser(), $meal);

		if($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				/** @var Participant $participant */
				$participant = $form->getData();
				$em = $this->getDoctrine()->getManager();
				$em->persist($participant);
				$em->flush();

				return $this->redirect($this->generateUrl('MealzMealBundle_Meal_show', array('meal' => $meal->getId())));
			}
		}

		return $this->render('MealzMealBundle:Participant:comment.html.twig', array(
			'meal' => $meal,
			'form' => $form->createView()
		));
	}

	protected function getCommentForm(Zombie $user, Meal $meal) {
		$participant = $this->getParticipantRepository()->getParticipantByUserAndMeal($user, $meal);

		if($participant === NULL) {
			throw $this->createNotFoundException('You need to join the meal in order to comment.');
		}

		return $this->createFormBuilder($participant)
			->add('comment', 'textarea', array(
				'required' => FALSE
			))
			->add('save', 'submit')
			->getForm()
		;
	}

}