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


	public function newAction(Request $request, $date, $dish) {
		if(!$this->getUser()) {
			throw new AccessDeniedException();
		}
		$meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish);
		if(!$meal) {
			throw $this->createNotFoundException($this->get('translator')->trans('meal.does_not_exist',array(),'messages'));
		}
		if(!$this->getDoorman()->isUserAllowedToJoin($meal)) {
			throw new AccessDeniedException($this->get('translator')->trans('meal.not_allowed_to_join',array(),'messages'));
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
							sprintf($this->get('translator')->trans('meal.placeholder_joined',array(),'messages'), $participant->getGuestName()),
							'success'
						);
					} else {
						$this->addFlashMessage($this->get('translator')->trans('meal.you_joined',array(),'messages'), 'success');
					}
				} catch(ParticipantNotUniqueException $e) {
					$this->addFlashMessage($this->get('translator')->trans('meal.you_joined',array(),'messages'), 'danger');
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
		if(!$this->getDoorman()->isKitchenStaff() && $this->getProfile() !== $participant->getProfile()) {
			throw new AccessDeniedException();
		}

		$form = $this->createForm(new ParticipantForm(), $participant, array(
			'allow_guest' => $participant->isGuest(),
			'allow_cost_absorption' => $participant->isCostAbsorbed() || $this->getDoorman()->isUserAllowedToRequestCostAbsorption($participant->getMeal()),
		));

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

					$this->addFlashMessage($this->get('translator')->trans('changes_stored',array(),'messages'), 'success');
				} catch(ParticipantNotUniqueException $e) {
					$this->addFlashMessage($this->get('translator')->trans('error.edit.participant_exists',array(),'messages'), 'danger');
				}

				return $this->redirect($this->generateUrlTo($participant->getMeal()));
			}
		}

		return $this->render('MealzMealBundle:Participant:form.html.twig', array(
			'meal' => $participant->getMeal(),
			'form' => $form->createView()
		));
	}

	public function deleteAction(Request $request, Participant $participant) {
		if(!$this->getUser()) {
			throw new AccessDeniedException();
		}
		if($this->getProfile() !== $participant->getProfile() && !$this->getDoorman()->isKitchenStaff()) {
			throw new AccessDeniedException();
		}

		if(!$this->getDoorman()->isUserAllowedToLeave($participant->getMeal())) {
			throw new AccessDeniedException($this->get('translator')->trans('meal.not_allowed_to_leave',array(),'messages'));
		}

		$date = $participant->getMeal()->getDateTime()->format('Y-m-d');
		$dish = $participant->getMeal()->getDish()->getSlug();

		$em = $this->getDoctrine()->getManager();
		$em->remove($participant);
		$em->flush();

		if ($request->isXmlHttpRequest()) {
			$ajaxResponse = new JsonResponse();
			$ajaxResponse->setData(array(
				'addClass' => 'btn-success',
				'removeClass' => 'btn-danger',
				'text' => $this->get('translator')->trans('meal.join', array(), 'action'),
				'url' => $this->generateUrl('MealzMealBundle_Meal_join', array(
					'date' => $date,
					'dish' => $dish
				))
			));

			return $ajaxResponse;
		} else {
			return $this->redirect($this->generateUrlTo($participant->getMeal()));
		}
	}

}