<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Day;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Week;


class LoadWeeks extends AbstractFixture implements OrderedFixtureInterface {

	/**
	 * @var ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var int
	 */
	protected $counter = 0;

	function load(ObjectManager $manager) {
		$this->objectManager = $manager;

		$date = new \DateTime('');
		$maxDate = new \DateTime('+1 month');

		while($date < $maxDate) {
			$week = new Week();
			$week->setYear($date->format('Y'));
			$week->setCalendarWeek($date->format('W'));
			$this->objectManager->persist($week);
			$this->addReference('week-' . $this->counter++, $week);

			$date->modify('+1 week');
		}

		$this->objectManager->flush();
	}

	public function getOrder()
	{
		return 1;
	}


}