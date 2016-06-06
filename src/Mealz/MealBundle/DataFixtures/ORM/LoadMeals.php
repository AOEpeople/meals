<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Day;
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
	 * @var Day[]
	 */
	protected $days = array();

	/**
	 * @var int
	 */
	protected $counter = 0;

	function load(ObjectManager $manager) {
		$this->objectManager = $manager;
		$this->loadDishes();
		$this->loadDays();

		foreach ($this->days as $day) {
			$dish = null;
			for ($i = 0; $i <= 1; $i++) {
				// 2 meals a day
				$meal = new Meal();
				$meal->setDay($day);
				$meal->setDateTime($day->getDateTime());
				$dish = $this->getRandomDish($dish);
				$meal->setDish($dish);
				$meal->setPrice(mt_rand(290, 320) / 100);
				$this->objectManager->persist($meal);
				$this->addReference('meal-' . $this->counter++, $meal);
			}
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

	protected function loadDays()
	{
		foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
			if ($reference instanceof Day) {
				// we can't just use $reference here, because
				// getReference() does some doctrine magic that getReferences() does not
				$this->days[] = $this->getReference($referenceName);
			}
		}
	}

	/**
	 * @param Dish $previousDish
	 * @return Dish
	 */
	protected function getRandomDish($previousDish) {
		do {
			$key = array_rand($this->dishes);
			$dish = $this->dishes[$key];
		} while ($dish === $previousDish);

		return $dish;
	}

	public function getOrder()
	{
		return 5;
	}
}