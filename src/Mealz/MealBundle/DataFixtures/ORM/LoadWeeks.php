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

	function load(ObjectManager $manager)
	{
		$this->objectManager = $manager;

		foreach ($this->getData() as $record) {
			$week = new Week();
			$week->setYear($record['year']);
			$week->setCalendarWeek($record['calendarWeek']);
			$this->objectManager->persist($week);
			$this->addReference('week-' . $record['calendarWeek'], $week);
		}

		$this->objectManager->flush();
	}

	public function getData()
	{
		return [
			['year' => '2016', 'calendarWeek' => '41'],
			['year' => '2016', 'calendarWeek' => '42'],
			['year' => '2016', 'calendarWeek' => '43'],
			['year' => '2016', 'calendarWeek' => '44'],
			['year' => '2016', 'calendarWeek' => '45'],
			['year' => '2016', 'calendarWeek' => '46']
		];
	}

	public function getOrder()
	{
		return 1;
	}
}
