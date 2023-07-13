<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use RuntimeException;

/**
 * @extends ArrayCollection<int, Dish>
 */
class DishCollection extends ArrayCollection
{
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            if (false === ($item instanceof Dish)) {
                throw new RuntimeException('invalid argument; expected "Dish", got "' . gettype($item) . '"');
            }
        }

        parent::__construct($items);
    }

    public function containsDishVariation(): bool
    {
        foreach ($this as $item) {
            if (true === ($item instanceof DishVariation)) {
                return true;
            }
        }

        return false;
    }
}
