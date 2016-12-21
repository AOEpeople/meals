<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Category;

/**
 * Fixtures Load the Categories
 * Class LoadCategories
 * @package Mealz\MealBundle\DataFixtures\ORM
 */
class LoadCategories extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture
     */
    const ORDER_NUMBER = 4;

    protected $counter = 0;

    /**
     * load the Fixture
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $categories = array(
            'Others' => 'Sonstiges',
            'Vegetarian' => 'Vegetarisch',
            'Meat' => 'Fleisch',
        );

        foreach ($categories as $categoryEn => $categoryDe) {
            $category = new Category();
            $category->setTitleDe($categoryDe);
            $category->setTitleEn($categoryEn);
            $manager->persist($category);
            $this->addReference('category-'.$this->counter++, $category);
        }

        $manager->flush();
    }

    /**
     * get the Order of Fixtures Loading
     * @return mixed
     */
    public function getOrder()
    {
        /**
         * load as fourth
         */
        return self::ORDER_NUMBER;
    }


}