<?php

namespace Mealz\MealBundle\Controller;

use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Form\DishForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\VarDumper\VarDumper;

class DishController extends BaseController {

	public function listAction()
	{
		if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}

		$dishes = $this->getDishRepository()->getSortedDishes();

		return $this->render('MealzMealBundle:Dish:list.html.twig', array(
			'dishes' => $dishes
		));
	}

	public function getDishFormAction(Request $request) {
		if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}

		$dish = $this->getDoctrine()->getRepository('MealzMealBundle:Dish')->findOneBy(array('slug' => $slug));

		if (!$dish) {
			$dish = new Dish();
		}

		$form = $this->createForm(new DishForm(), $dish, array(
			'action' => $this->generateUrl('MealzMealBundle_Dish_new'),
		));
		$renderedForm = $this->render(
			'MealzMealBundle:Dish/partials:form.html.twig',
			array(
				'form' => $form->createView()
			))->getContent()
		;

		return new JsonResponse($renderedForm);
	}

	public function getEditDishFormAction(Request $request, $slug)
	{
		if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}

		$dish = $this->getDoctrine()->getRepository('MealzMealBundle:Dish')->findOneBy(array('slug' => $slug));

		if (!$dish) {
			// @TODO: json response 404
			throw $this->createNotFoundException();
		}

		$form = $this->createForm(new DishForm(), $dish, array(
			'action' => $this->generateUrl('MealzMealBundle_Dish_edit', array('slug' => $slug)),
		));

		$renderedForm = $this->render(
			'MealzMealBundle:Dish/partials:form.html.twig',
			array(
				'form' => $form->createView()
			))->getContent();

		return new JsonResponse($renderedForm);
	}

	public function newAction(Request $request) {
		if(!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}

		$dish = new Dish();

		$form = $this->createForm(new DishForm(), $dish);

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

		return $this->redirectToRoute('MealzMealBundle_Dish');
	}

	public function editAction(Request $request, $slug) {
		if(!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}

		$dish = $this->getDoctrine()->getRepository('MealzMealBundle:Dish')->findOneBy(array('slug' => $slug));
		if(!$dish) {
			throw $this->createNotFoundException();
		}

		$form = $this->createForm(new DishForm(), $dish);

		// handle form submission
		if($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				$em = $this->getDoctrine()->getManager();
				$em->persist($dish);
				$em->flush();

				$this->addFlashMessage('Dish was modified.', 'success');
			}
		}

		return $this->redirectToRoute('MealzMealBundle_Dish');
	}

	public function deleteAction($slug) {
		if(!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}
		$dish = $this->getDoctrine()->getRepository('MealzMealBundle:Dish')->findOneBy(array('slug' => $slug));
		if(!$dish) {
			throw $this->createNotFoundException();
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

		return $this->redirectToRoute('MealzMealBundle_Dish');
	}
}