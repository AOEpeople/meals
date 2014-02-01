<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\MealRepository;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MealController extends BaseController {

	/**
	 * @return MealRepository
	 */
	public function getMealRepository() {
		return $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
	}


	public function indexAction() {
		$meals = $this->getMealRepository()->getSortedMeals(
			new \DateTime(),   // minDate
			NULL,              // maxDate
			4,                 // limit
			array(
				'load_dish' => TRUE
			)
		);

		return $this->render('MealzMealBundle:Meal:index.html.twig', array(
			'meals' => $meals
		));
	}

	public function listAction() {
		$meals = $this->getMealRepository()->getSortedMeals(
			new \DateTime('-2 hours'),
			NULL,
			NULL,
			array(
				'load_dish' => TRUE,
				'load_participants' => TRUE,
			)
		);

		return $this->render('MealzMealBundle:Meal:list.html.twig', array(
			'meals' => $meals
		));
	}

	public function showAction($meal) {
		$meal = $this->getMealRepository()->findOneById($meal, array('load_dish' => TRUE, 'load_participants' => TRUE));

		if(!$meal) {
			throw $this->createNotFoundException('The given meal does not exist');
		}

		return $this->render('MealzMealBundle:Meal:show.html.twig', array(
			'meal' => $meal
		));
	}

	/**
	 * let the currently logged in user join the given meal
	 *
	 * @param Meal $meal
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
	 */
	public function joinAction(Meal $meal) {
		if(!$this->getUser()) {
			throw new AccessDeniedException();
		}
		if(!$this->getDoorman()->isUserAllowedToJoin($meal)) {
			throw new AccessDeniedException('You are not allowed to join this meal.');
		}

		try {
			$participant = new Participant();
			$participant->setProfile($this->getProfile());
			$participant->setMeal($meal);

			$em = $this->getDoctrine()->getManager();
			$em->transactional(function(EntityManager $em) use($participant) {
				$em->persist($participant);
				$em->flush();
			});

			$this->get('session')->getFlashBag()->add(
				'success',
				'You joined as participant to the meal.'
			);
		} catch (ParticipantNotUniqueException $e) {
			$this->addFlashMessage('You are already joining this meal.', 'info');
		}

		return $this->redirect($this->generateUrlTo($meal));
	}

}