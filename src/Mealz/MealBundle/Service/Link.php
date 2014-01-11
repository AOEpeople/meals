<?php

namespace Mealz\MealBundle\Service;
use Mealz\MealBundle\Entity\Meal;
use Mealz\UserBundle\Entity\Zombie;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * Central service to link to actions
 *
 * This is done for portability. Should we decide to switch the urls to use the title instead of the
 * id, then we just have to change this here and not in all templates. Also this is easier readable in templates.
 */
class Link {

	/**
	 * @var Router
	 */
	protected $router;

	public function __construct(Router $router) {
		$this->router = $router;
	}

	public function link($object, $action = NULL, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH) {
		if($object instanceof Meal) {
			return $this->linkMeal($object, $action, $referenceType);
		} else {
			throw new \InvalidArgumentException(sprintf(
				'linking a %s object is not configured.',
				get_class($object)
			));
		}
	}

	public function linkMeal(Meal $meal, $action = NULL, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH) {
		$action = $action ?: 'show';
		if($action === 'show' || $action === 'join' || $action === 'leave' || $action === 'comment') {
			return $this->router->generate('MealzMealBundle_Meal_' . $action, array('meal' => $meal->getId()), $referenceType);
		} else {
			throw new \InvalidArgumentException(sprintf(
				'linking to "%s" action on a %s object is not configured.',
				$action,
				get_class($meal)
			));
		}
	}


}