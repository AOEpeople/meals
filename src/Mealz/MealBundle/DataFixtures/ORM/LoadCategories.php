<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use App\Mealz\MealBundle\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCategories extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture.
     */
    private const ORDER_NUMBER = 4;

    protected int $counter = 0;

    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Others' => 'Sonstiges',
            'Vegetarian' => 'Vegetarisch',
            'Meat' => 'Fleisch',
        ];

        foreach ($categories as $categoryEn => $categoryDe) {
            $category = new Category();
            $category->setTitleDe($categoryDe);
            $category->setTitleEn($categoryEn);
            $manager->persist($category);
            $this->addReference('category-' . $this->counter++, $category);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        // load as fourth
        return self::ORDER_NUMBER;
    }
}
