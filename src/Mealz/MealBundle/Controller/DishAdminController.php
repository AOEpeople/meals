<?php

namespace Mealz\MealBundle\Controller;

use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Form\Type\DishAdminForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DishAdminController extends BaseController {

	public function newAction(Request $request) {
		if(!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}

		$dish = new Dish();

		$form = $this->createForm(new DishAdminForm(), $dish);

		// handle form submission
		if($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				$em = $this->getDoctrine()->getManager();
				$em->persist($dish);
				$em->flush();

				$this->addFlashMessage('Dish has been added.', 'success');

				return $this->redirect($this->generateUrlTo($dish));
			}
		}

		return $this->render('MealzMealBundle:DishAdmin:form.html.twig', array(
//			'dish' => $dish,
			'form' => $form->createView()
		));
	}

	public function editAction(Request $request, Dish $dish) {
		if(!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}

		$form = $this->createForm(new DishAdminForm(), $dish);

		// handle form submission
		if($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				$em = $this->getDoctrine()->getManager();
				$em->persist($dish);
				$em->flush();

				$this->addFlashMessage('Dish was modified.', 'success');

				return $this->redirect($this->generateUrlTo($dish));
			}
		}

		return $this->render('MealzMealBundle:DishAdmin:form.html.twig', array(
			'dish' => $dish,
			'form' => $form->createView()
		));
	}

	public function deleteAction(Dish $dish) {
		if(!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}

		$em = $this->getDoctrine()->getManager();

		if($dish->getMeals()->count() > 0) {
			// if there are meals assigned: just hide this record, but do not delete it
			$dish->setEnabled(FALSE);
			$em->persist($dish);
			$em->flush();
			$this->addFlashMessage(sprintf('Record "%s" was hidden.', $dish->getTitle()), 'success');
		} else {
			// else: no need to keep an unused record
			$em->remove($dish);
			$em->flush();

			$this->addFlashMessage(sprintf('Record "%s" was deleted.', $dish->getTitle()), 'success');
		}

		return $this->redirect($this->generateUrl('MealzMealBundle_Dish'));
	}
}