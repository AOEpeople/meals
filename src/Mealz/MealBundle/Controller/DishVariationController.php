<?php

namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Entity\DishVariation;
use Mealz\MealBundle\Form\DishVariationForm;
use Symfony\Component\HttpFoundation\JsonResponse;
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
	 * @param  Request $request
	 * @param  integer $slug
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function newAction(Request $request, $slug)
	{
		$this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

		/** @var \Mealz\MealBundle\Entity\Dish $dish */
		$dish = $this->getDishRepository()->find($slug);

		if (!$dish) {
			throw $this->createNotFoundException();
		}

		$dishVariation = new DishVariation();
		$dishVariation->setDish($dish);
		$dishVariationForm = $this->createForm(
			new DishVariationForm(),
			$dishVariation,
			['action' => $this->generateUrl('MealzMealBundle_DishVariation_new', ['slug' => $dish->getId()])]
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

	public function editAction(Request $request, $slug)
	{
		$this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

		/** @var \Mealz\MealBundle\Entity\DishVariationRepository $dishVariationRepository */
		$dishVariationRepository = $this->getDoctrine()->getRepository('MealzMealBundle:DishVariation');

		/** @var \Mealz\MealBundle\Entity\DishVariation $dish */
		$dishVariation = $dishVariationRepository->find($slug);

		if (!$dishVariation) {
			throw $this->createNotFoundException();
		}

		$dishVariationForm = $this->createForm(
			new DishVariationForm(),
			$dishVariation,
			['action' => $this->generateUrl('MealzMealBundle_DishVariation_edit', ['slug' => $dishVariation->getId()])]
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
	 * @param  integer $slug
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function deleteAction($slug)
	{
		$this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

		/** @var \Mealz\MealBundle\Entity\DishVariationRepository $dishRepository */
		$dishVariationRepository = $this->getDoctrine()->getRepository('MealzMealBundle:DishVariation');

		/** @var \Mealz\MealBundle\Entity\DishVariation $dishVariation */
		$dishVariation = $dishVariationRepository->find($slug);

		if (!$dishVariation) {
			throw $this->createNotFoundException();
		}

		$dishVariation->setEnabled(FALSE);

		$this->persistEntity($dishVariation);

		$message = $this->get('translator')->trans(
			'dish.hidden',
			array('%dish%' => $dishVariation->getDescription()),
			'messages'
		);
		$this->addFlashMessage($message, 'success');

		return $this->redirectToRoute('MealzMealBundle_Dish');
	}

	private function persistEntity($entity)
	{
		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();
		$em->persist($entity);
		$em->flush();
	}
}
