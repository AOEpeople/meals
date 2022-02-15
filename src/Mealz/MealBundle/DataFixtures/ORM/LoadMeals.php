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
use RuntimeException;

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
    protected array $dishes = [];

    /**
     * @var array<int, Day>
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
        $dish = null;

        foreach ($this->days as $key => $day) {
            $normDayIndex = ($key + 10) % 10;

            // every alt. Mon. and Wed. get one meal with simple dish and two meals with dish variations
            if (self::IDX_ALT_MONDAY === $normDayIndex || self::IDX_ALT_WEDNESDAY === $normDayIndex) {
                $this->loadNewMeal($day, $this->dishes[0]);
                $dish = $this->getRandomDishWithVariations();
                foreach ($dish->getVariations()->slice(0, 2) as $dishVariation) {
                    $this->loadNewMeal($day, $dishVariation);
                }

                continue;
            }

            // add 2 meals with simple dishes (no variations)
            for ($i = 0; $i < 2; ++$i) {
                $dish = $this->getRandomDish($dish);
                $this->loadNewMeal($day, $dish);
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

    private function getRandomDishWithVariations(): Dish
    {
        $dishesWithVariations = [];

        foreach ($this->dishes as $dish) {
            if ($dish->hasVariations()) {
                $dishesWithVariations[] = $dish;
            }
        }

        if (0 === count($dishesWithVariations)) {
            throw new RuntimeException('dish with variations not found');
        }

        $randomDishKey = array_rand($dishesWithVariations);

        return $dishesWithVariations[$randomDishKey];
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
