<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\Query;

class MealController extends BaseController {

	public function indexAction() {
		/** @var Query $query */
		$query = $this->getDoctrine()->getManager()->createQuery('
			SELECT m,d
			FROM MealzMealBundle:Meal m
			JOIN m.dish d
			WHERE m.dateTime > :min_date
			ORDER BY m.dateTime ASC
		');
		$query->setParameter('min_date', new \DateTime());
		$query->setMaxResults(4);
		$meals = $query->execute();

		return $this->render('MealzMealBundle:Meal:index.html.twig', array(
			'meals' => $meals
		));
	}

	public function listAction() {
		/** @var Query $query */
		$query = $this->getDoctrine()->getManager()->createQuery('
			SELECT m,d,p,u
			FROM MealzMealBundle:Meal m
			JOIN m.dish d
			LEFT JOIN m.participants p
			LEFT JOIN p.user u
			WHERE m.dateTime > :min_date
			ORDER BY m.dateTime ASC
		');
		$query->setParameter('min_date', new \DateTime('-2 hours'));
		$meals = $query->execute();

		return $this->render('MealzMealBundle:Meal:list.html.twig', array(
			'meals' => $meals
		));
	}

	public function showAction($meal) {
		/** @var Query $query */
		$query = $this->getDoctrine()->getManager()->createQuery('
			SELECT m,d,p,u
			FROM MealzMealBundle:Meal m
			JOIN m.dish d
			LEFT JOIN m.participants p
			LEFT JOIN p.user u
			WHERE m.id = :meal_id
		');
		$query->setMaxResults(1);
		$meal = $query->execute(array('meal_id' => intval($meal)));
		if(!$meal) {
			throw $this->createNotFoundException('The given meal does not exist');
		} else {
			$meal = current($meal);
		}

		return $this->render('MealzMealBundle:Meal:show.html.twig', array(
			'meal' => $meal
		));
	}

}