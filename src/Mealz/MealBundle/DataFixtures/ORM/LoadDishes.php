<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Entity\Dish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadDishes extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture.
     */
    private const ORDER_NUMBER = 5;

    protected ObjectManager $objectManager;

    protected array $categories = [];

    protected int $counter = 0;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;

        $this->loadCategories();

        $this->addDish('Braaaaaiiinnnzzzzzz', 'Braaaaaiiinnnzzzzzz DE');
        $this->addDish('Tasty Worms', 'Tasty Worms DE');
        $this->addDish('Innards', 'Innards DE');
        $this->addDish('Fish (so juicy sweat)', 'Fish (so juicy sweat) DE');
        $this->addDish('Limbs', 'Limbs DE');
        $this->addDish(
            'Century Eggs, paired with a compote of seasonal berries and rye bread',
            'Century Eggs, serviert mit einem Kompott aus Beeren der Saison und Roggenbrot',
            'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, '
            . 'sed diam nonumy eirmod tempor invidunt ut labore et dolore '
            . 'magna aliquyam erat, sed diam voluptua.'
        );
        $this->addDish(
            'Limbs oh la la la (oven backed) + Finger food with a slimy sweet and sour sauce',
            'Limbs oh la la la (Ofen gebacken) + Finger food mit einer schlammigen Süß-Sauer-Soße'
        );

        $this->objectManager->flush();
    }

    public function loadCategories(): void
    {
        foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
            if ($reference instanceof Category) {
                // we can't just use $reference here, because
                // getReference() does some doctrine magic that getReferences() does not
                $this->categories[] = $this->getReference($referenceName);
            }
        }
    }

    public function getOrder(): int
    {
        // load as fifth
        return self::ORDER_NUMBER;
    }

    protected function addDish(string $titleEN, string $titleDE, string $descEN = null, string $descDE = null): void
    {
        $dish = new Dish();
        $dish->setPrice(3.60);
        $dish->setTitleEn($titleEN);
        $dish->setTitleDe($titleDE);
        $dish->setDescriptionEn('Description - ' . $titleEN);
        $dish->setDescriptionDe($descDE ?? $descEN ?? 'Beschreibung - ' . $titleDE);
        $randomCategory = (0 === count($this->categories)) ? null : $this->categories[array_rand($this->categories, 1)];
        $dish->setCategory($randomCategory);
        $this->objectManager->persist($dish);
        $this->addReference('dish-' . $this->counter++, $dish);
    }
}
