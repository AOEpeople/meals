<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\MealRepository;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use Mealz\MealBundle\Form\MealProfileForm;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Mealz\MealBundle\Entity\Week;

class MealController extends BaseController {

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

	/**
	 * @param Meal $meal
	 * @return Form
	 */
	private function getAddParticipantForm($meal) {
		return $this->createForm(new MealProfileForm($this->get('translator')->trans('meal.participant.add',array(),'action')),null,array('action' => $this->generateUrlTo($meal,"join_someone")));
	}

	/**
	 * @param $date
	 * @param $dish
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function showAction($date, $dish) {
		$meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish, array('load_dish' => TRUE, 'load_participants' => TRUE));
		if(!$meal) {
			throw $this->createNotFoundException($this->get('translator')->trans('meal.does_not_exist',array(),'messages'));
		}

		if($this->getDoorman()->isKitchenStaff()) {
			// form that allows kitchen staff to add arbitrary users
			$addParticipantForm = $this->getAddParticipantForm($meal);
		} else {
			$addParticipantForm = NULL;
		}

		return $this->render('MealzMealBundle:Meal:show.html.twig', array(
			'meal' => $meal,
			'addParticipantForm' => $addParticipantForm ? $addParticipantForm->createView() : NULL
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

	public function indexAction() {
		/** @var WeekRepository $weekRepository */
		$weekRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Week');

		$currentWeek = $weekRepository->getCurrentWeek();
		if (null === $currentWeek) {
			$currentWeek = $this->createWeek(new \DateTime());
		}

		$nextWeek = $weekRepository->getNextWeek();
		if (null === $nextWeek) {
			$nextWeek = $this->createWeek(new \DateTime('next week'));
		}

		$weeks = array(
			array($currentWeek, $weekRepository->getWeeksMealCount($currentWeek)),
			array($nextWeek, $weekRepository->getWeeksMealCount($nextWeek))
		);

		return $this->render('MealzMealBundle:Meal:index.html.twig', array(
			'weeks' => $weeks
		));
	}

	/**
	 * let the currently logged in user join the given meal
	 *
	 * @param Request $request
	 * @param string $date
	 * @param string $dish
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function joinAction(Request $request, $date, $dish) {

		if(!$this->getUser()) {
			return new JsonResponse(null, 401);
		}

		$meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish);

		if(!$meal) {
			return new JsonResponse(null, 404);
		}

		if(!$this->getDoorman()->isUserAllowedToJoin($meal)) {
			return new JsonResponse(null, 403);
		}
		try {
			$profile = $this->getProfile();
			$participant = new Participant();
			$participant->setProfile($profile);
			$participant->setMeal($meal);

			$em = $this->getDoctrine()->getManager();
			$em->transactional(function (EntityManager $em) use ($participant) {
				$em->persist($participant);
				$em->flush();
			});
		} catch (ParticipantNotUniqueException $e) {
			return new JsonResponse(null, 422);
		}

		$ajaxResponse = new JsonResponse();
		$ajaxResponse->setData(array(
			'participantsCount' => $this->getParticipantRepository()->getTotalParticipationsForMeal($meal),
			'url' => $this->generateUrl('MealzMealBundle_Participant_delete', array(
				'participant' => $participant->getId()
			))
		));

		return $ajaxResponse;
	}

	/**
	 * let some user join the given meal
	 *
	 * @param Request $request
	 * @param string $date
	 * @param string $dish
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function joinSomeoneAction(Request $request, $date, $dish) {
		if (!$this->getUser()) {
			throw new AccessDeniedException();
		}
		if (!$this->getDoorman()->isKitchenStaff()) {
			throw new AccessDeniedException($this->get('translator')->trans('You are not allowed to add users to this meal.', array(), 'general'));
		}
		$meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish);
		if (!$meal) {
			throw $this->createNotFoundException($this->get('translator')->trans('meal.does_not_exist', array(), 'messages'));
		}

		$form = $this->getAddParticipantForm($meal);
		$form->handleRequest($request);

		if ($form->isValid()) {
			/** @var Profile $profile */
			$profile = $form->get("participant")->getData();

			try {

				$participant = new Participant();
				$participant->setProfile($profile);
				$participant->setMeal($meal);

				$em = $this->getDoctrine()->getManager();
				$em->transactional(function (EntityManager $em) use ($participant) {
					$em->persist($participant);
					$em->flush();
				});

				$this->addFlashMessage(
					$this->get('translator')->trans('meal.placeholder_joined', array('%username%' => $profile->getUsername()), 'messages'),
					'success'
				);
			} catch (ParticipantNotUniqueException $e) {
				$this->addFlashMessage(
					$this->get('translator')->trans('meal.placeholder_already_joined', array('%username%' => $profile->getUsername()), 'messages'),
					'info'
				);
			}
		} else {

		}



		return $this->redirect($this->generateUrlTo($meal));
	}


	protected function groupByDay(\DateTime $startTime, $meals) {
		$return = array();

		$return[$startTime->format('Y-m-d')] = array();

		for ($i = 0; $i < 5; $i++) {
			$day = clone($startTime);
			$day->modify('+' . $i . ' days');
			$return[$day->format('Y-m-d')] = array();
		}

		foreach($meals as $meal) {
			/** @var Meal $meal */
			$day = $meal->getDateTime()->format('Y-m-d');
			if(!array_key_exists($day, $return)) {
				/*
				 * @TODO: throw new Error?
				 */
				$return[$day] = array();
			}
			$return[$day][] = $meal;
		}

		return $return;
	}

	protected function getWeek(\DateTime $startTime) {
		$endTime = clone $startTime;
		$endTime->modify('+4 days');
		$endTime->setTime(23,59,59);

		$meals = $this->getMealRepository()->getSortedMeals(
			$startTime,
			$endTime,
			null,
			array(
				'load_dish' => true,
				'load_participants' => true,
			)
		);

		$days = $this->groupByDay($startTime, $meals);

		$week = new Week();
		$week->setStartTime($startTime);
		$week->setEndTime($endTime);
		$week->setMealsCount(count($meals));
		$week->setDays($days);

		return $week;
	}

	private function createWeek(\DateTime $dateTime)
	{
		$week = new Week();
		$week->setCalendarWeek($dateTime->format('W'));
		$week->setYear($dateTime->format('Y'));

		$em = $this->getDoctrine()->getManager();
		$em->persist($week);
		$em->flush();
		$em->refresh($week);

		return $week;
	}
}