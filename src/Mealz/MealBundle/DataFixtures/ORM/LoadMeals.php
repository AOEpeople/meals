<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\Meal;


class LoadMeals extends AbstractFixture implements OrderedFixtureInterface {

	/**
	 * @var ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $dishes = array();

	/**
	 * @var int
	 */
	protected $counter = 0;

	function load(ObjectManager $manager) {
		$this->objectManager = $manager;
		$this->loadDishes();

		$date = new \DateTime('-7 days 12:00:00');
		$maxDate = new \DateTime('+1 month');

		while($date < $maxDate) {
			for($i=0; $i<=1; $i++) {
				// 2 meals a day
				$meal = new Meal();
				$meal->setDateTime(clone $date);
				$meal->setDish($this->getRandomDish());
				$this->objectManager->persist($meal);
				$this->addReference('meal-' . $this->counter++, $meal);
			}

			$date->modify('+1 day');
		}

		$this->objectManager->flush();
	}

	protected function loadDishes() {
		foreach($this->referenceRepository->getReferences() as $referenceName=>$reference) {
			if($reference instanceof Dish) {
				// we can't just use $reference here, because
				// getReference() does some doctrine magic that getReferences() does not
				$this->dishes[] = $this->getReference($referenceName);
			}
		}
	}

	/**
	 * @return Dish
	 */
	protected function getRandomDish() {
		$key = array_rand($this->dishes);
		return $this->dishes[$key];
	}

	public function getOrder()
	{
		return 2;
	}
}