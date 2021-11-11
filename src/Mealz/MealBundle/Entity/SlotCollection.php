<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ArrayCollection<int, Slot>
 */
class SlotCollection extends ArrayCollection
{
    public function __construct(array $elements = [])
    {
        $slots = $this->getValidSlots($elements);

        parent::__construct($slots);
    }

    /**
     * @param array $elements
     *
     * @return list<Slot>
     */
    private function getValidSlots(array $elements): array
    {
        $slots = [];

        foreach ($elements as $element) {
            if ($element instanceof Slot) {
                $slots[] = $element;
            }
        }

        return $slots;
    }
}
