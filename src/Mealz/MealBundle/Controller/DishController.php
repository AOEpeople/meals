<?php

namespace Mealz\MealBundle\Controller;

use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\DishRepository;

class DishController extends BaseController {

	/**
	 * @return DishRepository
	 */
	public function getMealRepository() {
		$repository = $this->getDoctrine()->getRepository('MealzMealBundle:Dish');
		$repository->setCurrentLocale($this->getRequest()->getLocale());

		return $repository;
	}

	public function listAction() {
		$dishes = $this->getMealRepository()->getSortedDishes();

		return $this->render('MealzMealBundle:Dish:list.html.twig', array(
			'dishes' => $dishes
		));
	}

}