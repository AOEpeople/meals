<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\MealRepository;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use Mealz\MealBundle\Form\MealProfileForm;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
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

	private function getParticipantForm($meal) {
		return $this->createForm(new MealProfileForm($this->get('translator')->trans('Add participant',array(),'general')),null,array('action' => $this->generateUrlTo($meal,"join")));
	}

	public function showAction($date, $dish) {
		$meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish, array('load_dish' => TRUE, 'load_participants' => TRUE));
		if(!$meal) {
			throw $this->createNotFoundException($this->get('translator')->trans('The given meal does not exist',array(),'general'));
		}

		$form = $this->getParticipantForm( $meal);

		return $this->render('MealzMealBundle:Meal:show.html.twig', array(
			'meal' => $meal, 'form' => $form->createView()
		));
	}

	public function dayAction($day) {
		try {
			$day = new \DateTime($day);
		} catch(\Exception $e) {
			throw $this->createNotFoundException($this->get('translator')->trans('Invalid Date',array(),'general'), $e);
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
			throw $this->createNotFoundException($this->get('translator')->trans('Invalid Date',array(),'general'), $e);
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
	public function joinAction(Request $request, $date, $dish) {
		if(!$this->getUser()) {
			throw new AccessDeniedException();
		}
		$meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish);
		if(!$meal) {
			throw $this->createNotFoundException($this->get('translator')->trans('The given meal does not exist',array(),'general'));
		}
		if(!$this->getDoorman()->isUserAllowedToJoin($meal)) {
			throw new AccessDeniedException($this->get('translator')->trans('You are not allowed to join this meal.',array(),'general'));
		}

		/** @var Form $form */
		$form = $this->getParticipantForm( $meal);
		$form->handleRequest($request);

		$profile=null;
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$profile = $form->get("participant")->getData();
		}


		try {

			$participant = new Participant();

			$participant->setProfile(($profile === null) ? $this->getProfile():$profile);

			$participant->setMeal($meal);

			$em = $this->getDoctrine()->getManager();
			$em->transactional(function(EntityManager $em) use($participant) {
				$em->persist($participant);
				$em->flush();
			});

			$this->get('session')->getFlashBag()->add(
				'success',$profile->getUsername().' '.
				$this->get('translator')->trans('joined as participant to the meal.',array(),'general')
			);
		} catch (ParticipantNotUniqueException $e) {
			$this->addFlashMessage($profile->getUsername().' '.$this->get('translator')->trans('is already joining this meal.',array(),'general'), 'info');
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