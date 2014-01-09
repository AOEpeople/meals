<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Mealz\MealBundle\Entity\Meal;

class MealController extends Controller {

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
			SELECT m,d
			FROM MealzMealBundle:Meal m
			JOIN m.dish d
			WHERE m.dateTime > :min_date
			ORDER BY m.dateTime ASC
		');
		$query->setParameter('min_date', new \DateTime('-2 hours'));
		$meals = $query->execute();

		return $this->render('MealzMealBundle:Meal:list.html.twig', array(
			'meals' => $meals
		));
	}

	public function showAction(Meal $meal) {
		return $this->render('MealzMealBundle:Meal:show.html.twig', array(
			'meal' => $meal
		));
	}

}