<?php


namespace Mealz\MealBundle\Controller;


use Mealz\MealBundle\Entity\CategoryRepository;
use Mealz\MealBundle\Entity\DishRepository;
use Mealz\MealBundle\Entity\MealRepository;
use Mealz\MealBundle\Entity\ParticipantRepository;
use Mealz\MealBundle\Service\Doorman;
use Mealz\MealBundle\Service\Link;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class BaseController extends Controller {
	/**
	 * @return MealRepository
	 */
	public function getMealRepository()
	{
		return $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
	}

	/**
	 * @return DishRepository
	 */
	public function getDishRepository()
	{
		$repository = $this->getDoctrine()->getRepository('MealzMealBundle:Dish');
		$repository->setCurrentLocale($this->getRequest()->getLocale());

		return $repository;
	}

	/**
	 * @return ParticipantRepository
	 */
	public function getParticipantRepository() {
		return $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
	}

	/**
	 * @return CategoryRepository
	 */
	public function getCategoryRepository()
	{
		return $this->getDoctrine()->getRepository('MealzMealBundle:Category');
	}

	/**
	 * @return Doorman
	 */
	protected function getDoorman() {
		return $this->get('mealz_meal.doorman');
	}

	/**
	 * @return Profile|null
	 */
	protected function getProfile() {
		return $this->getUser() ? $this->getUser()->getProfile() : NULL;
	}

	/**
	 * @param $object
	 * @param null $action
	 * @param bool $referenceType
	 * @return string
	 */
	public function generateUrlTo($object, $action = NULL, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH) {
		/** @var Link $linkService */
		$linkService = $this->get('mealz_meal.link');
		return $linkService->link($object, $action, $referenceType);
	}

	/**
	 * @param $message
	 * @param $severity  "danger", "warning", "info", "success"
	 */
	public function addFlashMessage($message, $severity) {
		$this->get('session')->getFlashBag()->add($severity, $message);
	}


}