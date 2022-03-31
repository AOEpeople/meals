<?php

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadDishVariations extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture.
     */
    private const ORDER_NUMBER = 6;

    protected ObjectManager $objectManager;

    /**
     * @var Dish[]
     */
    protected array $dishes = [];

    protected int $counter = 0;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;
        $this->loadDishes();

        foreach ($this->dishes as $key => $dish) {
            // Create two variation for each dish EXCEPT THE FIRST ONE
            if ($key > 0) {
                for ($i = 0; $i < 2; ++$i) {
                    $dishVariation = $this->getDishVariation($dish);
                    $this->objectManager->persist($dishVariation);
                    $this->addReference('dishVariation-' . $this->counter++, $dishVariation);
                }
            }
        }

        $this->objectManager->flush();
    }

    public function getOrder(): int
    {
        // load as sixth
        return self::ORDER_NUMBER;
    }

    protected function loadDishes(): void
    {
        foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
            if ($reference instanceof Dish) {
                // we can't just use $reference here, because
                // getReference() does some doctrine magic that getReferences() does not
                $this->dishes[] = $this->getReference($referenceName);
            }
        }
    }

    private function getDishVariation(Dish $dish): DishVariation
    {
        $dummyPrefix = ' #v' . (count($dish->getVariations()) + 1);
        $dishVariation = new DishVariation();
        $dishVariation->setTitleDe($dish->getTitleDe() . $dummyPrefix);
        $dishVariation->setTitleEn($dish->getTitleEn() . $dummyPrefix);
        $dishVariation->setParent($dish);
        $dishVariation->setPrice(3.60);

        $dish->getVariations()->add($dishVariation);

        return $dishVariation;
    }
}
