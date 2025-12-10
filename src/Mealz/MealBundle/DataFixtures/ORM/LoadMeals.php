<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use App\Mealz\AccountingBundle\Entity\Price;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Override;

final class LoadMeals extends Fixture implements OrderedFixtureInterface
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
    private array $dishesWithVar = [];

    /**
     * @var Dish[]
     */
    private array $dishesWithoutVar = [];

    /**
     * @var array<int, Day>
     */
    private array $days = [];

    private int $counter = 0;
    private bool $containsOldPrices;

    public function __construct($containsOldPrices = false)
    {
        $this->containsOldPrices = $containsOldPrices;
    }


    /**
     * @throws Exception
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;
        $this->loadDishes();
        $this->loadDays();
        $lastDishWithVar = null;
        $lastDishWithoutVar = null;
        $price = new Price();
        $year = 2025;
        if ($this->containsOldPrices) {
            $year = 2024;
        }
        $price->setPriceValue(4.4);
        $price->setYear($year);
        $price->setPriceCombinedValue(6.4);
        $manager->persist($price);

        foreach ($this->days as $key => $day) {
            $normDayIndex = ($key + 10) % 10;

            // every alt. Mon. and Wed. get one meal with simple dish and two meals with dish variations
            if (self::IDX_ALT_MONDAY === $normDayIndex || self::IDX_ALT_WEDNESDAY === $normDayIndex) {
                $dish = $this->getRandomDishWithoutVariations($lastDishWithoutVar);
                $this->loadNewMeal($day, $dish, $price);
                $lastDishWithoutVar = $dish;

                $dish = $this->getRandomDishWithVariations($lastDishWithVar);
                foreach ($dish->getVariations()->slice(0, 2) as $dishVariation) {
                    $this->loadNewMeal($day, $dishVariation, $price);
                }
                $lastDishWithVar = $dish;

                continue;
            }

            // add 2 meals with simple dishes (no variations)
            for ($i = 0; $i < 2; ++$i) {
                $dish = $this->getRandomDishWithoutVariations($lastDishWithoutVar);
                $this->loadNewMeal($day, $dish, $price);
                $lastDishWithoutVar = $dish;
            }
        }

        $this->objectManager->flush();
    }

    /**
     * @throws Exception
     */
    public function loadNewMeal(Day $day, Dish $dish, Price $price): void
    {
        $meal = new Meal($dish, $price, $day);

        $this->objectManager->persist($meal);
        $this->addReference('meal-' . $this->counter++, $meal);
    }

    #[Override]
    public function getOrder(): int
    {
        // load as seventh
        return self::ORDER_NUMBER;
    }

    protected function loadDishes(): void
    {
        foreach ($this->referenceRepository->getReferencesByClass()[Dish::class] as $referenceName => $reference) {
            if (($reference instanceof Dish) && !($reference instanceof DishVariation)) {
                // we can't just use $reference here, because
                // getReference() does some doctrine magic that getReferencesByClass() does not
                /** @var Dish $dish */
                $dish = $this->getReference($referenceName, Dish::class);

                if ($dish->hasVariations()) {
                    $this->dishesWithVar[] = $dish;
                } else {
                    $this->dishesWithoutVar[] = $dish;
                }
            }
        }
    }

    protected function loadDays(): void
    {
        foreach (array_keys($this->referenceRepository->getReferencesByClass()[Day::class]) as $key) {
            // we can't just use $reference here, because
            // getReference() does some doctrine magic that getReferences() does not
            $this->days[] = $this->getReference($key, Day::class);
        }
    }

    private function getRandomDishWithVariations(?Dish $previousDish = null): Dish
    {
        do {
            $randomDishKey = array_rand($this->dishesWithVar);
            $dish = $this->dishesWithVar[$randomDishKey];
        } while ($dish === $previousDish);

        return $dish;
    }

    /**
     * Get random Dishes without Variations.
     */
    private function getRandomDishWithoutVariations(?Dish $previousDish = null): Dish
    {
        do {
            $randomDishKey = array_rand($this->dishesWithoutVar);
            $dish = $this->dishesWithoutVar[$randomDishKey];
        } while ($dish === $previousDish);

        return $dish;
    }
}
