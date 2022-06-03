<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use Doctrine\Bundle\FixturesBundle\Fixture;
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
     * Index of alternative Monday and Wednesday.
     */
    private const IDX_ALT_MONDAY = 0;
    private const IDX_ALT_WEDNESDAY = 7;

    protected ObjectManager $objectManager;

    /**
     * @var Dish[]
     */
    private array $dishesWithVariations = [];

    /**
     * @var Dish[]
     */
    private array $dishesWithoutVariations = [];

    /**
     * @var array<int, Day>
     */
    private array $days = [];

    private int $counter = 0;

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
        $lastDishWithVariations = null;
        $lastDishWithoutVariations = null;

        foreach ($this->days as $key => $day) {
            $normDayIndex = ($key + 10) % 10;

            // every alt. Mon. and Wed. get one meal with simple dish and two meals with dish variations
            if (self::IDX_ALT_MONDAY === $normDayIndex || self::IDX_ALT_WEDNESDAY === $normDayIndex) {
                $dish = $this->getRandomDishWithoutVariations($lastDishWithoutVariations);
                $this->loadNewMeal($day, $dish);
                $lastDishWithoutVariations = $dish;

                $dish = $this->getRandomDishWithVariations($lastDishWithVariations);
                foreach ($dish->getVariations()->slice(0, 2) as $dishVariation) {
                    $this->loadNewMeal($day, $dishVariation);
                }
                $lastDishWithVariations = $dish;

                continue;
            }

            // add 2 meals with simple dishes (no variations)
            for ($i = 0; $i < 2; ++$i) {
                $dish = $this->getRandomDishWithoutVariations($lastDishWithoutVariations);
                $this->loadNewMeal($day, $dish);
                $lastDishWithoutVariations = $dish;
            }
        }

        $this->objectManager->flush();
    }

    /**
     * @throws Exception
     */
    public function loadNewMeal(Day $day, Dish $dish): void
    {
        $meal = new Meal($dish, $day);

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
                /** @var Dish $dish */
                $dish = $this->getReference($referenceName);

                if ($dish->hasVariations()) {
                    $this->dishesWithVariations[] = $dish;
                } else {
                    $this->dishesWithoutVariations[] = $dish;
                }
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

    private function getRandomDishWithVariations(?Dish $previousDish = null): Dish
    {
        do {
            $randomDishKey = array_rand($this->dishesWithVariations);
            $dish = $this->dishesWithVariations[$randomDishKey];
        } while ($dish === $previousDish);

        return $dish;
    }

    /**
     * Get random Dishes without Variations.
     */
    private function getRandomDishWithoutVariations(?Dish $previousDish = null): Dish
    {
        do {
            $randomDishKey = array_rand($this->dishesWithoutVariations);
            $dish = $this->dishesWithoutVariations[$randomDishKey];
        } while ($dish === $previousDish);

        return $dish;
    }
}
