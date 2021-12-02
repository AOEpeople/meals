<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use RuntimeException;

/**
 * @extends ArrayCollection<int, Meal>
 */
class MealCollection extends ArrayCollection
{
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            if (!($item instanceof Meal)) {
                throw new RuntimeException('invalid argument; expected "Meal", got "'.gettype($item).'"');
            }
        }

        parent::__construct($items);
    }

    /**
     * Checks if collection contains a meal that can no longer be booked, but can be taken over from someone.
     */
    public function containsOpenMeal(): bool
    {
        $now = new DateTime();

        /** @var Meal $meal */
        foreach ($this->getValues() as $meal) {
            if ($meal->getDateTime() > $now) {
                return true;
            }
        }

        return false;
    }
}
