<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Day;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\DishVariation;
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

    /**
     * @param ObjectManager $manager
     */
    function load(ObjectManager $manager) {
		$this->objectManager = $manager;
		$this->loadDishes();
		$this->loadDays();

		foreach ($this->days as $key => $day) {
			$dish = null;

			// that should be next week Wednesday which should be available for selection
            if ($key == 7) {
                // add once 3 Options per Day, 1 Dish without variations and 1 with 2 variations
                $this->loadNewMeal($day, $this->dishes[0]); // first Dish was loaded without variations
                $dishVariations = $this->getRandomDishWithVariations();
                foreach ($dishVariations as $key => $dishVariation) {
                    if ($key < 2) { // in case there is more then 2 variations
                        $this->loadNewMeal($day, $dishVariation);
                    }
                }
            }
            else {
                for ($i = 0; $i <= 1; $i++) {
                    // 2 meals a day
                    $dish = $this->getRandomDish($dish);
                    $this->loadNewMeal($day, $dish);
                }
            }
		}

		$this->objectManager->flush();
	}

    /**
     * @param Day $day
     * @param Dish $dish
     */
    public function loadNewMeal($day, $dish)
    {
        $meal = new Meal();
        $meal->setDay($day);
        $meal->setDateTime($day->getDateTime());
        $meal->setDish($dish);
        $meal->setPrice($dish->getPrice());
        $this->objectManager->persist($meal);
        $this->addReference('meal-' . $this->counter++, $meal);
    }


    protected function loadDishes() {
		foreach($this->referenceRepository->getReferences() as $referenceName=>$reference) {
		    // TODO: this is actually not working as designed, will load both Dish and Variations, please discuss with Dirk
			if(($reference instanceof Dish) && !($reference instanceof DishVariation)) {
				// we can't just use $reference here, because
				// getReference() does some doctrine magic that getReferences() does not
				$this->dishes[] = $this->getReference($referenceName);
			}
			// if there is a parent then this is a Variation
//			if (method_exists($reference, 'getParent')) {
//			    if ($reference->getParent()) {
//                    $this->dishVariations[] = $this->getReference($referenceName);
//                }
//            }
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
	 * @return DishVariation[]
	 */
	protected function getRandomDishWithVariations() {
	    do {
            $key = array_rand($this->dishes);
            $dish = $this->dishes[$key];
        } while (count($dish->getVariations()) == 0);

	    return $dish->getVariations();
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
		return 7;
	}
}