<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class LoadMeals extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture.
     */
    private const ORDER_NUMBER = 7;

    /**
     * index of first weeks monday.
     */
    private const FIRST_MONDAY = 0;

    /**
     * index of second weeks wednesday.
     */
    private const SECOND_WEDNESDAY = 7;

    protected ObjectManager $objectManager;

    /**
     * @var Dish[]
     */
    protected array $dishes = [];

    /**
     * @var Day[]
     */
    protected array $days = [];

    protected int $counter = 0;

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;
        $this->loadDishes();
        $this->loadDays();

        foreach ($this->days as $key => $day) {
            $dish = null;
            // that should be next week Wednesday which should be available for selection
            if (self::SECOND_WEDNESDAY === $key || self::FIRST_MONDAY === $key) {
                // add once 3 Options per Day, 1 Dish without variations and 1 with 2 variations
                $this->loadNewMeal($day, $this->dishes[0]); // first Dish was loaded without variations
                $dishVariations = $this->getRandomDishWithVariations();
                foreach ($dishVariations as $k => $dishVariation) {
                    if ($k < 2) { // in case there is more then 2 variations
                        $this->loadNewMeal($day, $dishVariation);
                    }
                }
            } else {
                for ($i = 0; $i <= 1; ++$i) {
                    // 2 meals a day
                    $dish = $this->getRandomDish($dish);
                    $this->loadNewMeal($day, $dish);
                }
            }
        }

        $this->objectManager->flush();
    }

    /**
     * @throws Exception
     */
    public function loadNewMeal(Day $day, Dish $dish): void
    {
        $meal = new Meal();
        $meal->setDay($day);

        $meal->setDateTime(clone $day->getDateTime());
        $meal->setDish($dish);
        $meal->setPrice($dish->getPrice());
        $this->objectManager->persist($meal);
        $this->addReference('meal-' . $this->counter++, $meal);
    }

    public function getOrder(): int
    {
        // load as seventh
        return self::ORDER_NUMBER;
    }

    protected function loadDishes(): void
    {
        foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
            if (($reference instanceof Dish) && !($reference instanceof DishVariation)) {
                // we can't just use $reference here, because
                // getReference() does some doctrine magic that getReferences() does not
                $this->dishes[] = $this->getReference($referenceName);
            }
        }
    }

    protected function loadDays(): void
    {
        foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
            if ($reference instanceof Day) {
                // we can't just use $reference here, because
                // getReference() does some doctrine magic that getReferences() does not
                $this->days[] = $this->getReference($referenceName);
            }
        }
    }

    protected function getRandomDishWithVariations(): Collection
    {
        $dishVariations = new ArrayCollection();
        $dishesWithVariations = [];

        foreach ($this->dishes as $dish) {
            if ($dish->hasVariations()) {
                $dishesWithVariations[] = $dish;
            }
        }

        if (count($dishesWithVariations)) {
            $randomDishKey = array_rand($dishesWithVariations);
            $dishVariations = $dishesWithVariations[$randomDishKey]->getVariations();
        }

        return $dishVariations;
    }

    /**
     * Get random Dishes without Variations.
     */
    protected function getRandomDish(?Dish $previousDish): Dish
    {
        do {
            $key = array_rand($this->dishes);
            $dish = $this->dishes[$key];
        } while ($dish === $previousDish);

        return $dish;
    }
}
