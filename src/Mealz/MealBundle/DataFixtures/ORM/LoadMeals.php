<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Day;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\DishVariation;
use Mealz\MealBundle\Entity\Meal;

/**
 * Fixtures load the Meals
 * Class LoadMeals
 * @package Mealz\MealBundle\DataFixtures\ORM
 */
class LoadMeals extends AbstractFixture implements OrderedFixtureInterface
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
     * @var Day[]
     */
    protected $days = array();

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * load the Object
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
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
            } else {
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
     * load the new Meals
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
        $this->addReference('meal-'.$this->counter++, $meal);
    }

    /**
     * get the Fixtures load Order
     * @return mixed
     */
    public function getOrder()
    {
        /**
         * load as seventh
         */
        return 7;
    }

    /**
     * load the Dishes
     */
    protected function loadDishes()
    {
        foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
            if (($reference instanceof Dish) && !($reference instanceof DishVariation)) {
                // we can't just use $reference here, because
                // getReference() does some doctrine magic that getReferences() does not
                $this->dishes[] = $this->getReference($referenceName);
            }
        }
    }

    /**
     * load the Days
     */
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
     * get random Dishes with Variations
     * @return DishVariation[]
     */
    protected function getRandomDishWithVariations()
    {
        do {
            $key = array_rand($this->dishes);
            $dish = $this->dishes[$key];
        } while (count($dish->getVariations()) == 0);

        return $dish->getVariations();
    }

    /**
     * get random Dishes without Variations
     * @param Dish $previousDish
     * @return Dish
     */
    protected function getRandomDish($previousDish)
    {
        do {
            $key = array_rand($this->dishes);
            $dish = $this->dishes[$key];
        } while ($dish === $previousDish);

        return $dish;
    }

}