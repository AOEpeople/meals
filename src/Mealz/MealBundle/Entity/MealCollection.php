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
    /**
     * @param Meal[] $elements
     */
    public function __construct(array $elements = [])
    {
        foreach ($elements as $item) {
            if (false === ($item instanceof Meal)) {
                throw new RuntimeException('invalid argument; expected "Meal", got "' . gettype($item) . '"');
            }
        }

        parent::__construct($elements);
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

    /**
     * Checks if collection contains a bookable meal.
     */
    public function containsBookableMeal(): bool
    {
        $now = new DateTime();

        /** @var Meal $meal */
        foreach ($this->getValues() as $meal) {
            if ($meal->getLockDateTime() > $now) {
                return true;
            }
        }

        return false;
    }

    /**
     * Group meals by meal type, i.e. simple or combined.
     *
     * @param bool $combinedFirst Weather or not to place combined meals before simple meals in result
     */
    public function groupByType(bool $combinedFirst = true): MealCollection
    {
        $simple = [];
        $combined = [];

        /** @var Meal $meal */
        foreach ($this->getValues() as $meal) {
            if (true === $meal->isCombinedMeal()) {
                $combined[] = $meal;
            } else {
                $simple[] = $meal;
            }
        }

        $result = $combinedFirst ? array_merge($combined, $simple) : array_merge($simple, $combined);

        return new MealCollection($result);
    }
}
