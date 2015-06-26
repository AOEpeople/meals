<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\MealRepository;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use Mealz\MealBundle\Form\MealParticipantForm;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MealController extends BaseController {

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
			'meals' => $meals,
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

	public function showAction($date, $dish) {
		$meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish, array('load_dish' => TRUE, 'load_participants' => TRUE));
		if(!$meal) {
			throw $this->createNotFoundException('The given meal does not exist');
		}

		$dishes = $this->getDishRepository()->getSortedDishes();
		$form = $this->createForm(new MealParticipantForm(), $dishes);

		return $this->render('MealzMealBundle:Meal:show.html.twig', array(
			'meal' => $meal, 'form' => $form->createView()
		));
	}

	public function dayAction($day) {
		try {
			$day = new \DateTime($day);
		} catch(\Exception $e) {
			throw $this->createNotFoundException('Invalid Date', $e);
		}

		$meals = $this->getMealRepository()->getSortedMealsOnDay($day);
		$participants = $this->getParticipantRepository()->getParticipantsOnDay($day);

		return $this->render('MealzMealBundle:Meal:day.html.twig', array(
			'meals' => $meals,
			'participants' => $participants,
			'day' => $day,
		));
	}

	public function weekAction($week) {
		try {
			$startTime = new \DateTime($week);
		} catch(\Exception $e) {
			throw $this->createNotFoundException('Invalid Date', $e);
		}

		$endTime = clone $startTime;
		$endTime->modify('+1 week');

		$meals = $this->getMealRepository()->getSortedMeals(
			$startTime,
			$endTime,
			NULL,
			array(
				'load_dish' => TRUE,
				'load_participants' => TRUE,
			)
		);

		return $this->render('MealzMealBundle:Meal:week.html.twig', array(
			'meals' => $meals,
			'days' => $this->groupByDay($meals),
			'week' => $week,
		));
	}

	/**
	 * let the currently logged in user join the given meal
	 *
	 * @param Meal $meal
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
	 */
	public function joinAction($date, $dish) {
		if(!$this->getUser()) {
			throw new AccessDeniedException();
		}
		$meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish);
		if(!$meal) {
			throw $this->createNotFoundException('The given meal does not exist');
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

	protected function groupByDay($meals) {
		$return = array();
		foreach($meals as $meal) {
			/** @var Meal $meal */
			$day = $meal->getDateTime()->format('Y-m-d');
			if(!array_key_exists($day, $return)) {
				$return[$day] = array();
			}
			$return[$day][] = $meal;
		}

		return $return;
	}

}