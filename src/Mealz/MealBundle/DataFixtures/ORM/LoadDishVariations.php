<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\DishVariation;

/**
 * @package Mealz\MealBundle\DataFixtures\ORM
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class LoadDishVariations extends AbstractFixture implements OrderedFixtureInterface
{
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

	/**
	 * @param ObjectManager $manager
	 */
	function load(ObjectManager $manager)
	{
		$this->objectManager = $manager;
		$this->loadDishes();

		/** @var \Mealz\MealBundle\Entity\Dish $dish */
		foreach ($this->dishes as $dish) {
			// Create two variation for each dish
			for ($i = 0; $i < 2; $i++) {
				$dishVariation = $this->getDishVariation($dish);
				$this->objectManager->persist($dishVariation);
				$this->addReference('dishVariation-' . $this->counter++, $dishVariation);
			}
		}

		$this->objectManager->flush();
	}

	/**
	 * @param  Dish $dish
	 * @return DishVariation
	 */
	private function getDishVariation(Dish $dish)
	{
		$dummyPrefix = '#v' . (count($dish->getVariations()) + 1);
		$dishVariation = new DishVariation();
        $dishVariation->setTitleDe($dish->getTitleDe());
        $dishVariation->setTitleEn($dish->getTitleEn());
		$dishVariation->setDescriptionDe($dish->getTitleDe() . $dummyPrefix);
		$dishVariation->setDescriptionEn($dish->getTitleEn() . $dummyPrefix);
		$dishVariation->setParent($dish);

		$dish->getVariations()->add($dishVariation);

		return $dishVariation;
	}

	/**
	 * @return void
	 */
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
	 * @return int
	 */
	public function getOrder()
	{
		return 5;
	}
}
