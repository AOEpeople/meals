<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\DishVariation;

/**
 * Load the Dish Variations
 * @package Mealz\MealBundle\DataFixtures\ORM
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class LoadDishVariations extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $dishes = array();

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * load the Object
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->objectManager = $manager;
        $this->loadDishes();

        /** @var \Mealz\MealBundle\Entity\Dish $dish */
        foreach ($this->dishes as $key => $dish) {
            // Create two variation for each dish EXCEPT THE FIRST ONE
            if ($key > 0) {
                for ($i = 0; $i < 2; $i++) {
                    $dishVariation = $this->getDishVariation($dish);
                    $this->objectManager->persist($dishVariation);
                    $this->addReference('dishVariation-'.$this->counter++, $dishVariation);
                }
            }
        }

        $this->objectManager->flush();
    }


    /**
     * get the Fixture Load Order
     * @return int
     */
    public function getOrder()
    {
        /**
         * load as sixth
         */
        return 6;
    }

    
    /**
     * load the dishes
     * @return void
     */
    protected function loadDishes()
    {
        foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
            if ($reference instanceof Dish) {
                // we can't just use $reference here, because
                // getReference() does some doctrine magic that getReferences() does not
                $this->dishes[] = $this->getReference($referenceName);
            }
        }
    }

    /**
     * @param  Dish $dish
     * @return DishVariation
     */
    private function getDishVariation(Dish $dish)
    {
        $dummyPrefix = ' #v'.(count($dish->getVariations()) + 1);
        $dishVariation = new DishVariation();
        $dishVariation->setTitleDe($dish->getTitleDe().$dummyPrefix);
        $dishVariation->setTitleEn($dish->getTitleEn().$dummyPrefix);
        $dishVariation->setParent($dish);
        $dishVariation->setPrice(3.2);

        $dish->getVariations()->add($dishVariation);

        return $dishVariation;
    }

}
