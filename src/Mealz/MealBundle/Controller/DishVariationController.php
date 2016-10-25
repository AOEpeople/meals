<?php

namespace Mealz\MealBundle\Controller;


use Mealz\MealBundle\Entity\DishVariation;
use Mealz\MealBundle\Form\DishVariationForm;

class DishVariationController extends BaseController
{
	/**
	 * @param  integer $slug
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function newAction($slug)
	{
		$dishRepository = $this->getDishRepository();
		/** @var \Mealz\MealBundle\Entity\Dish $dish */
		$dish = $dishRepository->find($slug);

		if ($dish) {
			$dishVariation = new DishVariation();
			$dishVariation->setDish($dish);
			$newVariationForm = $this->createForm(new DishVariationForm(), $dishVariation);

			return $this->render('MealzMealBundle:DishVariation:new.html.twig', [
				'form' => $newVariationForm->createView(),
				'dish' => $dish
			]);
		}
	}
//
//	public function editAction(Request $request)
//	{
//		//
//	}
//
//	public function updateAction(Request $request)
//	{
//		//
//	}
}
