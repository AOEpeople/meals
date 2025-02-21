<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Entity;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Override;

final class DishTest extends AbstractDatabaseTestCase
{
    private EntityManagerInterface $entityManager;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();

        /* @var EntityManagerInterface $entityManager */
        $this->entityManager = $this->getDoctrine()->getManager();
    }

    /**
     * @test
     */
    public function isNotCombinedDish(): void
    {
        $dish = new Dish();
        $dish->setTitleEn('Some tasty Dish');
        $dish->setTitleDe('Ein schmackhaftes Gericht');

        $this->entityManager->persist($dish);
        $this->entityManager->flush();
        $this->entityManager->refresh($dish);

        $this->assertNotEmpty($dish->getSlug());
        $this->assertFalse($dish->isCombinedDish());
    }

    /**
     * @test
     */
    public function isCombinedDish(): void
    {
        $dish = new Dish();
        $dish->setTitleEn('Combined Dish');
        $dish->setTitleDe('Kombi Gericht');

        $this->entityManager->persist($dish);
        $this->entityManager->flush();
        $this->entityManager->refresh($dish);

        $this->assertNotEmpty($dish->getSlug());
        $this->assertEquals(Dish::COMBINED_DISH_SLUG, $dish->getSlug());
        $this->assertTrue($dish->isCombinedDish());
    }
}
