<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Mealz\UserBundle\Entity\Zombie;


class LoadParticipants extends AbstractFixture implements OrderedFixtureInterface {

	/**
	 * @var ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $meals = array();

	/**
	 * @var array
	 */
	protected $users = array();

	function load(ObjectManager $manager) {
		$this->objectManager = $manager;
		$this->loadReferences();

		foreach($this->meals as $meal) {
			/** @var $meal Meal */
			$users = $this->getRandomUsers();
			foreach($users as $user) {
				/** @var $user Zombie */
				$participant = new Participant();
				$participant->setMeal($meal);
				$participant->setUser($user);

				$this->objectManager->persist($participant);
			}
		}
		$this->objectManager->flush();
	}

	protected function loadReferences() {
		foreach($this->referenceRepository->getReferences() as $referenceName=>$reference) {
			if($reference instanceof Meal) {
				// we can't just use $reference here, because
				// getReference() does some doctrine magic that getReferences() does not
				$this->meals[] = $this->getReference($referenceName);
			} elseif($reference instanceof Zombie) {
				$this->users[] = $this->getReference($referenceName);
			}
		}
	}

	/**
	 * @return array<Users>
	 */
	protected function getRandomUsers() {
		$number = rand(0,count($this->users));
		$users = array();

		if($number > 1) {
			foreach(array_rand($this->users, $number) as $user_key) {
				$users[] = $this->users[$user_key];
			}
		} elseif($number == 1) {
			$users[] = $this->users[array_rand($this->users)];
		}
		return $users;
	}

	public function getOrder()
	{
		return 3;
	}
}