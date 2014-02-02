<?php

namespace Mealz\MealBundle\Controller;

use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\DishRepository;

class DishController extends BaseController {

	public function listAction() {
		$dishes = $this->getDishRepository()->getSortedDishes();

		return $this->render('MealzMealBundle:Dish:list.html.twig', array(
			'dishes' => $dishes
		));
	}

}