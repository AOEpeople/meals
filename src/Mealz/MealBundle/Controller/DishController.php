<?php

namespace Mealz\MealBundle\Controller;

use Doctrine\ORM\Query;

class DishController extends BaseController {

	public function listAction() {
		/** @var Query $query */
		$query = $this->getDoctrine()->getManager()->createQuery('
			SELECT d
			FROM MealzMealBundle:Dish d
			WHERE d.enabled = TRUE
			ORDER BY d.title_en ASC
		');

		$dishes = $query->execute();

		return $this->render('MealzMealBundle:Dish:list.html.twig', array(
			'dishes' => $dishes
		));
	}

}