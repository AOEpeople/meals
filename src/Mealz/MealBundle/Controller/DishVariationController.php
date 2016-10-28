<?php

namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\EntityManager;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Dish variation controller.
 *
 * @package Mealz\MealBundle\Controller
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class DishVariationController extends BaseController
{
	/**
	 * Handles request to create a new dish variation.
	 *
	 * @param  Request $request
	 * @param  integer $dishId
	 * @return Response
	 */
	public function newAction(Request $request, $dishId)
	{
		$this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

		/** @var \Mealz\MealBundle\Entity\Dish $dish */
		$dish = $this->getDishRepository()->find($dishId);

		if (!$dish) {
			throw $this->createNotFoundException();
		}

		/** @var \Mealz\MealBundle\Entity\DishVariation $dishVariation */
		$dishVariation = $this->get('mealz_meal.dish_variation');
		$dishVariation->setDish($dish);

		$dishVariationForm = $this->createForm(
			$this->get('mealz_meal.form.dish_variation'),
			$dishVariation,
			['action' => $this->generateUrl('MealzMealBundle_DishVariation_new', ['dishId' => $dish->getId()])]
		);
		$dishVariationForm->handleRequest($request);

		if ($dishVariationForm->isSubmitted() && $dishVariationForm->isValid()) {
			$dishVariation = $dishVariationForm->getData();
			$this->persistEntity($dishVariation);

			$message = $this->get('translator')->trans(
				'entity.added',
				array('%entityName%' => $dishVariation->getDescription()),
				'messages'
			);
			$this->addFlashMessage($message, 'success');

			return $this->redirectToRoute('MealzMealBundle_Dish');
		}

		$renderedForm = $this->render('MealzMealBundle:DishVariation:new.html.twig', [
			'form' => $dishVariationForm->createView(),
			'dishVariation' => $dishVariation
		]);

		return new JsonResponse($renderedForm->getContent());
	}

	/**
	 * Handles request to update a dish variation.
	 *
	 * @param  Request $request
	 * @param  $dishVariationId
	 * @return Response
	 */
	public function editAction(Request $request, $dishVariationId)
	{
		$this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

		/** @var \Mealz\MealBundle\Entity\DishVariationRepository $dishVariationRepository */
		$dishVariationRepository = $this->getDoctrine()->getRepository('MealzMealBundle:DishVariation');

		/** @var \Mealz\MealBundle\Entity\DishVariation $dish */
		$dishVariation = $dishVariationRepository->find($dishVariationId);

		if (!$dishVariation) {
			throw $this->createNotFoundException();
		}

		$dishVariationForm = $this->createForm(
			$this->get('mealz_meal.form.dish_variation'),
			$dishVariation,
			['action' => $this->generateUrl('MealzMealBundle_DishVariation_edit', ['dishVariationId' => $dishVariation->getId()])]
		);
		$dishVariationForm->handleRequest($request);

		if ($dishVariationForm->isSubmitted() && $dishVariationForm->isValid()) {
			$dishVariation = $dishVariationForm->getData();
			$this->persistEntity($dishVariation);

			$message = $this->get('translator')->trans(
				'entity.modified',
				array('%entityName%' => $dishVariation->getDescription()),
				'messages'
			);
			$this->addFlashMessage($message, 'success');

			return $this->redirectToRoute('MealzMealBundle_Dish');
		}

		$renderedForm = $this->render('MealzMealBundle:DishVariation:new.html.twig', [
			'form' => $dishVariationForm->createView(),
			'dishVariation' => $dishVariation
		]);

		return new JsonResponse($renderedForm->getContent());
	}

	/**
	 * Handles request to delete a dish variation.
	 *
	 * @param  integer $dishVariationId
	 * @return Response
	 */
	public function deleteAction($dishVariationId)
	{
		$this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

		/** @var \Mealz\MealBundle\Entity\DishVariationRepository $dishRepository */
		$dishVariationRepository = $this->getDoctrine()->getRepository('MealzMealBundle:DishVariation');

		/** @var \Mealz\MealBundle\Entity\DishVariation $dishVariation */
		$dishVariation = $dishVariationRepository->find($dishVariationId);

		if (!$dishVariation) {
			throw $this->createNotFoundException();
		}

		$dishVariation->setEnabled(FALSE);

		$this->persistEntity($dishVariation);

		$message = $this->get('translator')->trans(
			'dish_variation.deleted',
			['%dishVariation%' => $dishVariation->getDescription()],
			'messages'
		);
		$this->addFlashMessage($message, 'success');

		return $this->redirectToRoute('MealzMealBundle_Dish');
	}

	/**
	 * Persists an entity object in database.
	 *
	 * @param object $entity
	 */
	private function persistEntity($entity)
	{
		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();
		$em->persist($entity);
		$em->flush();
	}
}
