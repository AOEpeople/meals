<?php

namespace Mealz\MealBundle\Controller;

use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Form\DishForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DishController extends BaseController {

	public function listAction()
	{
		if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}

		$dishes = $this->getDishRepository()->getSortedDishes(array(
			'load_category' => true
		));

		return $this->render('MealzMealBundle:Dish:list.html.twig', array(
			'dishes' => $dishes
		));
	}

	public function getEmptyFormAction() {
		if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}

		$dish = new Dish();
		$action = $this->generateUrl('MealzMealBundle_Dish_new');

		return new JsonResponse($this->getRenderedDishForm($dish, $action));
	}

	public function getPreFilledFormAction($slug)
	{
		if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}

		/* @var Dish $dish */
		$dish = $this->getDoctrine()->getRepository('MealzMealBundle:Dish')->findOneBy(array('slug' => $slug));

		if (!$dish) {
			return new JsonResponse(null, 404);
		}

		$action = $this->generateUrl('MealzMealBundle_Dish_edit', array('slug' => $slug));

		return new JsonResponse($this->getRenderedDishForm($dish, $action, true));
	}

	public function newAction(Request $request) {
		if(!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}

		$dish = new Dish();
		$dish->setPrice($this->getParameter('mealz.meal.price'));

		return $this->dishFormHandling($request, $dish, 'Dish has been added.');
	}

	public function editAction(Request $request, $slug) {
		if(!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
			throw new AccessDeniedException();
		}

		/* @var Dish $dish */
		$dish = $this->getDoctrine()->getRepository('MealzMealBundle:Dish')->findOneBy(array('slug' => $slug));
		if(!$dish) {
			throw $this->createNotFoundException();
		}

		return $this->dishFormHandling($request, $dish, 'Dish has been modified.');
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
			$this->addFlashMessage(sprintf('Dish "%s" has been hidden.', $dish->getTitle()), 'success');
		} else {
			// else: no need to keep an unused record
			$em->remove($dish);
			$em->flush();

			$this->addFlashMessage(sprintf('Dish "%s" has been deleted.', $dish->getTitle()), 'success');
		}

		return $this->redirectToRoute('MealzMealBundle_Dish');
	}

	private function getRenderedDishForm(Dish $dish, $action, $wrapInTr = false)
	{
		$form = $this->createForm(new DishForm(), $dish, array(
			'action' => $action,
		));

		if ($wrapInTr) {
			$template = "MealzMealBundle:Dish/partials:formTable.html.twig";
		} else {
			$template = "MealzMealBundle:Dish/partials:form.html.twig";
		}

		$renderedForm = $this->render($template, array('form' => $form->createView()));

		return $renderedForm->getContent();
	}

	private function dishFormHandling(Request $request, Dish $dish, $successMessage)
	{
		$form = $this->createForm(new DishForm(), $dish);

		// handle form submission
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				$em = $this->getDoctrine()->getManager();
				$em->persist($dish);
				$em->flush();

				$this->addFlashMessage($successMessage, 'success');
			} else {
				$dishes = $this->getDishRepository()->getSortedDishes(array(
					'load_category' => true
				));

				return $this->render('MealzMealBundle:Dish:list.html.twig', array(
					'dishes' => $dishes,
					'form' => $form->createView()
				));
			}
		}

		return $this->redirectToRoute('MealzMealBundle_Dish');
	}
}