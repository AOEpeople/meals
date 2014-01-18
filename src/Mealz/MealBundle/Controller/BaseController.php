<?php


namespace Mealz\MealBundle\Controller;


use Mealz\MealBundle\Service\Doorman;
use Mealz\MealBundle\Service\Link;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class BaseController extends Controller {

	/**
	 * @return Doorman
	 */
	protected function getDoorman() {
		return $this->get('mealz_meal.doorman');
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