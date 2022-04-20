<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use App\Mealz\MealBundle\Twig\Extension\Variation;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use RuntimeException;

/**
 * @extends ArrayCollection<int, DishVariationTree>
 */
class DishVariationTreeCollection extends ArrayCollection
{
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            if (!($item instanceof DishVariationTree)) {
                throw new RuntimeException('invalid argument; expected "DishVariationTree", got "' . gettype($item) . '"');
            }
        }

        parent::__construct($items);
    }

    public function findCorrespondingTree(Dish $dish): ?DishVariationTree
    {
        /** @var DishVariationTree $tree */
        foreach ($this->getValues() as $tree) {
            if ($dish === $tree->getParentDish() || $dish->getParent() === $tree->getParentDish()) {

                return $tree;
            }
        }

        return null;
    }

    public function addDishToCollection(Dish $dish): void {
        $tree = $this->findCorrespondingTree($dish);
        if(null === $tree) {
            $tree = new DishVariationTree($dish);
            $this->add($tree);
        } else {
            if ($dish instanceof DishVariation) {
                $tree->addVariation($dish);
            }
        }
    }

    public function addDayToCollection(Day $day): void {
        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            $this->addDishToCollection($meal->getDish());
        }
    }
}
