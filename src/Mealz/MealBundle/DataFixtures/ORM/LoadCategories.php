<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Category;

class LoadCategories extends AbstractFixture implements OrderedFixtureInterface
{
    protected $counter = 0;

    function load(ObjectManager $manager)
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
            $this->addReference('category-' . $this->counter++, $category);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 4;
    }


}