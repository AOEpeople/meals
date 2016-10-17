<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Day;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Week;


class LoadDays extends AbstractFixture implements OrderedFixtureInterface {

	/**
	 * @var ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Week[]
	 */
	protected $weeks = array();

	protected $counter = 0;

	function load(ObjectManager $manager) {
		$this->objectManager = $manager;
		$this->loadWeeks();

		foreach($this->weeks as $week) {
			$startTime = $week->getStartTime();

			$day = new Day();
			$day->setWeek($week);
			$day->setDateTime($startTime);
			$this->objectManager->persist($day);
			$this->addReference('day-' . $this->counter++, $day);

			for ($i = 1; $i < 5; $i++) {
				$day = new Day();
				$day->setWeek($week);
				$time = clone($startTime);
				$time->modify('+' . $i . ' days');
				$day->setDateTime($time);
				$this->objectManager->persist($day);
				$this->addReference('day-' . $this->counter++, $day);
			}
		}

		$this->objectManager->flush();
	}

	protected function loadWeeks()
	{
		foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
			if ($reference instanceof Week) {
				// we can't just use $reference here, because
				// getReference() does some doctrine magic that getReferences() does not
				$this->weeks[] = $this->getReference($referenceName);
			}
		}
	}

	public function getOrder()
	{
		return 2;
	}


}