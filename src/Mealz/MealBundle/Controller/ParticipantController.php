<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\ParticipantRepository;
use Mealz\MealBundle\Form\Type\ParticipantForm;
use Mealz\MealBundle\Form\Type\ParticipantGuestForm;
use Mealz\UserBundle\Entity\Zombie;
use Mealz\MealBundle\Entity\Meal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ParticipantController extends BaseController {

	/**
	 * @return ParticipantRepository
	 */
	protected function getParticipantRepository() {
		return $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
	}

	public function newAction(Request $request, Meal $meal) {
		if(!$this->getUser() instanceof Zombie) {
			throw new AccessDeniedException();
		}
		if(!$this->getDoorman()->isUserAllowedToJoin($meal)) {
			throw new AccessDeniedException('You are not allowed to join this meal.');
		}

		$participant = new Participant();
		$participant->setMeal($meal);
		$participant->setUser($this->getUser());
		$form = $this->createForm(
			new ParticipantForm(),
			$participant,
			array('allow_guest' => $this->getDoorman()->isUserAllowedToAddGuest($meal))
		);

		// handle form submission
		if($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				// that method ensures consistency by using a transaction
				$this->getParticipantRepository()->persist($participant);

				if($participant->isGuest()) {
					$this->addFlashMessage(
						sprintf('Added %s as participant to the meal.', $participant->getGuestName()),
						'success'
					);
				} else {
					$this->addFlashMessage('You joined as participant to the meal.', 'success');
				}


				return $this->redirect($this->generateUrlTo($meal));
			}
		}

		return $this->render('MealzMealBundle:Participant:form.html.twig', array(
			'meal' => $meal,
			'form' => $form->createView()
		));
	}

	public function editAction(Request $request, Participant $participant) {
		if(!$this->getUser() instanceof Zombie) {
			throw new AccessDeniedException();
		}
		if($this->getUser() !== $participant->getUser()) {
			throw new AccessDeniedException();
		}

		if($participant->isGuest()) {
			$form = $this->createForm(new ParticipantGuestForm(), $participant);
		} else {
			$form = $this->createForm(new ParticipantForm(), $participant, array('allow_guest' => FALSE));
		}

		// handle form submission
		if($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				// that method ensures consistency by using a transaction
				$this->getParticipantRepository()->persist($participant);

				$this->addFlashMessage('Your changes were stored.', 'success');

				return $this->redirect($this->generateUrlTo($participant->getMeal()));
			}
		}

		return $this->render('MealzMealBundle:Participant:form.html.twig', array(
			'meal' => $participant->getMeal(),
			'form' => $form->createView()
		));
	}

	public function deleteAction(Participant $participant) {
		if(!$this->getUser() instanceof Zombie) {
			throw new AccessDeniedException();
		}
		if($this->getUser() !== $participant->getUser()) {
			throw new AccessDeniedException();
		}
		if(!$this->getDoorman()->isUserAllowedToLeave($participant->getMeal())) {
			throw new AccessDeniedException('You are not allowed to leave this meal.');
		}

		$em = $this->getDoctrine()->getManager();
		$em->remove($participant);
		$em->flush();

		if($participant->isGuest()) {
			$this->addFlashMessage('You were removed as participant to the meal.', 'success');
		} else {
			$this->addFlashMessage(
				sprintf('Removed %s as participant to the meal.', $participant->getGuestName()),
				'success'
			);
		}


		return $this->redirect($this->generateUrlTo($participant->getMeal()));
	}

}