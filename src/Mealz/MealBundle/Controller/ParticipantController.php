<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use Mealz\MealBundle\Form\ParticipantForm;
use Mealz\MealBundle\Form\ParticipantGuestForm;
use Mealz\UserBundle\Entity\Profile;
use Mealz\MealBundle\Entity\Meal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ParticipantController extends BaseController {

	public function newAction(Request $request, $date, $dish) {
		if(!$this->getUser()) {
			throw new AccessDeniedException();
		}
		$meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish);
		if(!$meal) {
			throw $this->createNotFoundException('The given meal does not exist');
		}
		if(!$this->getDoorman()->isUserAllowedToJoin($meal)) {
			throw new AccessDeniedException('You are not allowed to join this meal.');
		}

		$participant = new Participant();
		$participant->setMeal($meal);
		$participant->setProfile($this->getProfile());
		$form = $this->createForm(
			new ParticipantForm(),
			$participant,
			array('allow_guest' => $this->getDoorman()->isUserAllowedToAddGuest($meal))
		);

		// handle form submission
		if($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				try {
					$em = $this->getDoctrine()->getManager();
					$em->transactional(function(EntityManager $em) use($participant) {
						$em->persist($participant);
						$em->flush();
					});

					if($participant->isGuest()) {
						$this->addFlashMessage(
							sprintf('Added %s as participant to the meal.', $participant->getGuestName()),
							'success'
						);
					} else {
						$this->addFlashMessage('You joined as participant to the meal.', 'success');
					}
				} catch(ParticipantNotUniqueException $e) {
					$this->addFlashMessage('A participant with the same properties already exists in the database.', 'danger');
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
		if(!$this->getUser()) {
			throw new AccessDeniedException();
		}
		if($this->getProfile() !== $participant->getProfile()) {
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
				try {
					$em = $this->getDoctrine()->getManager();
					$em->transactional(function(EntityManager $em) use($participant) {
						$em->persist($participant);
						$em->flush();
					});

					$this->addFlashMessage('Your changes were stored.', 'success');
				} catch(ParticipantNotUniqueException $e) {
					$this->addFlashMessage('The participant could not be changed, because a participant with the same properties is already in the database.', 'danger');
				}

				return $this->redirect($this->generateUrlTo($participant->getMeal()));
			}
		}

		return $this->render('MealzMealBundle:Participant:form.html.twig', array(
			'meal' => $participant->getMeal(),
			'form' => $form->createView()
		));
	}

	public function deleteAction(Participant $participant) {
		if(!$this->getUser()) {
			throw new AccessDeniedException();
		}
		if($this->getProfile() !== $participant->getProfile()) {
			throw new AccessDeniedException();
		}
		if(!$this->getDoorman()->isUserAllowedToLeave($participant->getMeal())) {
			throw new AccessDeniedException('You are not allowed to leave this meal.');
		}

		$em = $this->getDoctrine()->getManager();
		$em->remove($participant);
		$em->flush();

		if($participant->isGuest()) {
			$this->addFlashMessage(
				sprintf('Removed %s as participant to the meal.', $participant->getGuestName()),
				'success'
			);
		} else {
			$this->addFlashMessage('You were removed as participant to the meal.', 'success');
		}


		return $this->redirect($this->generateUrlTo($participant->getMeal()));
	}

}