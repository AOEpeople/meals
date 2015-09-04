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
			throw $this->createNotFoundException($this->get('translator')->trans('The given meal does not exist',array(),'general'));
		}
		if(!$this->getDoorman()->isUserAllowedToJoin($meal)) {
			throw new AccessDeniedException($this->get('translator')->trans('You are not allowed to join this meal.',array(),'general'));
		}

		$participant = new Participant();
		$participant->setMeal($meal);
		$participant->setProfile($this->getProfile());
		$form = $this->createForm(
			new ParticipantForm(),
			$participant,
			array(
				'allow_guest' => $this->getDoorman()->isUserAllowedToAddGuest($meal),
				'allow_cost_absorption' => $this->getDoorman()->isUserAllowedToAddGuest($meal) && $this->getDoorman()->isUserAllowedToRequestCostAbsorption($meal),
			)
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
							sprintf($this->get('translator')->trans('Added %s as participant to the meal.',array(),'general'), $participant->getGuestName()),
							'success'
						);
					} else {
						$this->addFlashMessage($this->get('translator')->trans('You joined as participant to the meal.',array(),'general'), 'success');
					}
				} catch(ParticipantNotUniqueException $e) {
					$this->addFlashMessage($this->get('translator')->trans('You joined as participant to the meal.',array(),'general'), 'danger');
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
			$form = $this->createForm(new ParticipantGuestForm(), $participant, array(
				'allow_cost_absorption' => $this->getDoorman()->isUserAllowedToRequestCostAbsorption($participant->getMeal()),
			));
		} else {
			$form = $this->createForm(new ParticipantForm(), $participant, array(
				'allow_guest' => FALSE
			));
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

					$this->addFlashMessage($this->get('translator')->trans('Your changes were stored.',array(),'general'), 'success');
				} catch(ParticipantNotUniqueException $e) {
					$this->addFlashMessage($this->get('translator')->trans('The participant could not be changed, because a participant with the same properties is already in the database.',array(),'general'), 'danger');
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
		if($this->getProfile() !== $participant->getProfile() && !$this->getDoorman()->isKitchenStaff()) {
			throw new AccessDeniedException();
		}

		if(!$this->getDoorman()->isUserAllowedToLeave($participant->getMeal())) {
			throw new AccessDeniedException($this->get('translator')->trans('You are not allowed to leave this meal.',array(),'general'));
		}

		$em = $this->getDoctrine()->getManager();
		$em->remove($participant);
		$em->flush();

		if($participant->isGuest()) {
			$this->addFlashMessage(
				sprintf($this->get('translator')->trans('Removed %s as participant to the meal.',array(),'general'), $participant->getGuestName()),
				'success'
			);
		} else {
			if ($this->getProfile() !== $participant->getProfile()) {
				$this->addFlashMessage($participant->getProfile()->getUsername().' '.$this->get('translator')->trans('removed as participant to the meal.',array(),'general'), 'success');
			} else {
				$this->addFlashMessage($participant->getProfile()->getUsername().' '.$this->get('translator')->trans('was removed as participant to the meal.',array(),'general'), 'success');
			}
		}


		return $this->redirect($this->generateUrlTo($participant->getMeal()));
	}

}