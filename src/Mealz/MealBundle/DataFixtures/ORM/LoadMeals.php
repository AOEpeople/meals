<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * Constant to declare load order of fixture
     */
    const ORDER_NUMBER = 7;

    /**
     * index of first weeks monday
     */
    const FIRST_MONDAY = 0;

    /**
     * index of second weeks wednesday
     */
    const SECOND_WEDNESDAY = 7;

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

    public function load(ObjectManager $manager)
    {
        $this->objectManager = $manager;
        $this->loadDishes();
        $this->loadDays();
        foreach ($this->days as $key => $day) {
            $dish = null;
            // that should be next week Wednesday which should be available for selection
            if ($key == self::SECOND_WEDNESDAY || $key == self::FIRST_MONDAY) {
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
        // make transactions more realistic (random second,NO identical Date)
        $date = clone $day->getDateTime();
        $date->modify('+' . mt_rand(1, 1400) . ' second');
        $meal->setDateTime($date);
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
        return self::ORDER_NUMBER;
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
     * Get random Dishes with Variations
     *
     * @return Collection
     */
    protected function getRandomDishWithVariations()
    {
        $dishVariations = new ArrayCollection();
        $dishesWithVariations = [];

        /** @var Dish $dish */
        foreach ($this->dishes as $dish) {
            if ($dish->hasVariations()) {
                $dishesWithVariations[] = $dish;
            }
        }

        if (count($dishesWithVariations)) {
            /** @var Dish $randomDishWithVariation */
            $randomDishKey = array_rand($dishesWithVariations);
            $dishVariations = $dishesWithVariations[$randomDishKey]->getVariations();
        }

        return $dishVariations;
    }

    /**
     * Get random Dishes without Variations
     *
     * @param Dish $previousDish previous dish
     *
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
