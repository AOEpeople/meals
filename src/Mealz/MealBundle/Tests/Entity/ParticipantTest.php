<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Entity;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use App\Mealz\UserBundle\Entity\Profile;
use Override;

final class ParticipantTest extends AbstractDatabaseTestCase
{
    private Participant $participant;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $meal = new Meal(new Dish(), new Day());
        $profile = new Profile();

        $this->participant = new Participant($profile, $meal);
    }

    /**
     * @test
     */
    public function combinedDishesIsEmptyInitially(): void
    {
        $this->assertEmpty($this->participant->getCombinedDishes());
    }

    /**
     * @test
     */
    public function combinedDishesIsEmptyWhenSetToNull(): void
    {
        $this->participant->setCombinedDishes(null);
        $this->assertEmpty($this->participant->getCombinedDishes());
    }

    /**
     * @test
     */
    public function combinedDishesIsEmptyForEmptyDishes(): void
    {
        $this->participant->setCombinedDishes([]);
        $this->assertEmpty($this->participant->getCombinedDishes());
    }

    /**
     * @test
     */
    public function combinedDishesIsACopy(): void
    {
        $dishes = [
            new Dish(),
            new Dish(),
            new Dish(),
        ];

        $this->participant->setCombinedDishes($dishes);

        $dishCollection = $this->participant->getCombinedDishes();
        $dishCollection->remove(0);
        $this->assertCount(2, $dishCollection);
        $this->assertCount(3, $this->participant->getCombinedDishes());

        $dishCollection->add(new Dish());
        $this->assertCount(3, $dishCollection);
        $this->assertCount(3, $this->participant->getCombinedDishes());

        $this->participant->getCombinedDishes()->clear();
        $this->assertCount(3, $this->participant->getCombinedDishes());

        $this->participant->getCombinedDishes()->add(new Dish());
        $this->assertCount(3, $this->participant->getCombinedDishes());

        $this->participant->getCombinedDishes()->remove(2);
        $this->assertCount(3, $this->participant->getCombinedDishes());
    }
}
