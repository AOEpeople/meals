<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Category;
use Mealz\MealBundle\Entity\Dish;

/**
 * Fixtures Load the Dishes
 * Class LoadDishes
 * @package Mealz\MealBundle\DataFixtures\ORM
 */
class LoadDishes extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture
     */
    const ORDER_NUMBER = 5;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $categories = array();

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * load the Fixture
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->objectManager = $manager;

        $this->loadCategories();

        $this->addDish('Braaaaaiiinnnzzzzzz', 'Braaaaaiiinnnzzzzzz DE');
        $this->addDish('Tasty Worms', 'Tasty Worms DE');
        $this->addDish('Innards', 'Innards DE');
        $this->addDish('Fish (so juicy sweat)', 'Fish (so juicy sweat) DE');
        $this->addDish('Limbs', 'Limbs DE');

        $this->objectManager->flush();
    }

    /**
     * load the Categories
     */
    public function loadCategories()
    {
        foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
            if ($reference instanceof Category) {
                // we can't just use $reference here, because
                // getReference() does some doctrine magic that getReferences() does not
                $this->categories[] = $this->getReference($referenceName);
            }
        }
    }

    /**
     * get the Order of Fixtures loading
     * @return mixed
     */
    public function getOrder()
    {
        /**
         * load as fifth
         */
        return self::ORDER_NUMBER;
    }

    /**
     * add the dishes
     * @param $title
     * @param $titleDe
     */
    protected function addDish($title, $titleDe)
    {
        $dish = new Dish();
        $dish->setPrice(3.20);
        $dish->setTitleEn($title);
        $dish->setTitleDe($titleDe);
        $dish->setDescriptionEn('Description - '.$title);
        $dish->setDescriptionDe('Beschreibung - '.$titleDe);
        $randomCategory = (count($this->categories) == 0) ? null : $this->categories[array_rand($this->categories, 1)];
        $dish->setCategory($randomCategory);
        $this->objectManager->persist($dish);
        $this->addReference('dish-'.$this->counter++, $dish);
    }

}