<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Entity;

use App\Mealz\AccountingBundle\Entity\Price;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use Override;
use PHPUnit\Framework\TestCase;

final class DayTest extends TestCase
{
    private Day $day;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->day = new Day();
    }

    /**
     * @test
     */
    public function mealsEmpty(): void
    {
        $this->assertEmpty($this->day->getMeals());
    }

    /**
     * @test
     *
     * @testdox Add meals to a day
     */
    public function addMeal(): void
    {
        $dateTime = new \DateTimeImmutable('now');
        for ($i = 0; $i < 10; ++$i) {
            $price = new Price();
            $price->setYear((int)$dateTime->format('Y') - $i);
            $price->setPriceValue($i + 1);
            $price->setPriceCombinedValue($i + 2);
            $meal = new Meal(new Dish(), $price, $this->day);
            $this->day->addMeal($meal);
            $this->assertCount($i + 1, $this->day->getMeals());
            /** @var Meal $mealFromDay */
            $mealFromDay = $this->day->getMeals()->get($i);
            $this->assertEqualsWithDelta($meal->getPrice(), $mealFromDay->getPrice(), 0.1);
        }
    }

    /**
     * @test
     *
     * @testdox Remove meals from a day
     */
    public function removeMeal(): void
    {
        $meals = null;
        $numberOfMeals = 10;
        $dateTime = new \DateTimeImmutable('now');
        for ($i = 0; $i < $numberOfMeals; ++$i) {
            $price = new Price();
            $price->setYear((int)$dateTime->format('Y') - $i);
            $price->setPriceValue($i + 1.99);
            $price->setPriceCombinedValue($i + 2.99);
            $meal = new Meal(new Dish(), $price, $this->day);
            $meals[] = $meal;
            $this->day->addMeal($meal);
        }

        $mealKeys = array_keys($meals);
        shuffle($mealKeys);
        $this->assertCount(count($meals), $mealKeys);

        $randomOrderMeals = [];
        foreach ($mealKeys as $idx) {
            $randomOrderMeals[] = $meals[$idx];
        }

        $this->assertCount(count($meals), $randomOrderMeals);

        /** @var Meal $meal */
        $idx = 0;
        foreach ($randomOrderMeals as $meal) {
            $this->day->removeMeal($meal);
            $this->assertEqualsWithDelta($meal->getPrice()->getPriceValue(), $meals[$mealKeys[$idx]]->getPrice()->getPrice(), 0.1);
            --$numberOfMeals;
            ++$idx;
            $this->assertCount($numberOfMeals, $this->day->getMeals());
        }
    }
}
