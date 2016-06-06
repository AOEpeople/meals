<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Category;
use Mealz\MealBundle\Entity\Dish;


class LoadDishes extends AbstractFixture implements OrderedFixtureInterface {

	/**
	 * @var ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $categories = array();

	/**
	 * @var int
	 */
	protected $counter = 0;

	function load(ObjectManager $manager) {
		$this->objectManager = $manager;

		$this->loadCategories();

		$this->addDish('Braaaaaiiinnnzzzzzz with Tomato-Sauce', 'Braaaaaiiinnnzzzzzz with Tomato-Sauce DE');
		$this->addDish('Tasty Worms', 'Tast Worms DE');
		$this->addDish('Braaaaaiiinnnzzzzzz with Cheese-Sauce', 'Braaaaaiiinnnzzzzzz with Cheese-Sauce DE');
		$this->addDish('Fish (so juicy sweat)', 'Fish (so juicy sweat) DE');
		$this->addDish('Limbs', 'Limbs DE');

		$this->objectManager->flush();
	}

	protected function addDish($title, $titleDe) {
		$dish = new Dish();
		$dish->setTitleEn($title);
		$dish->setTitleDe($titleDe);
		$randomCategory = $this->categories[array_rand($this->categories, 1)];
		$dish->setCategory($randomCategory);
		$this->objectManager->persist($dish);
		$this->addReference('dish-' . $this->counter++, $dish);
	}

	public function loadCategories()
	{
		foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
			if ($reference instanceof Category) {
				// we can't just use $reference here, because
				// getReference() does some doctrine magic that getReferences() does not
				$this->categories[] = $this->getReference($referenceName);
			}
		}
	}

	public function getOrder()
	{
		return 4;
	}
}