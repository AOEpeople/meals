<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use RuntimeException;

/**
 * @extends ArrayCollection<int, EventParticipation>
 */
class EventCollection extends ArrayCollection
{
    /**
     * @param EventParticipation[] $elements
     */
    public function __construct(array $elements = [])
    {
        foreach ($elements as $item) {
            if (false === ($item instanceof EventParticipation)) {
                throw new RuntimeException('invalid argument; expected "EventParticipation", got "' . gettype($item) . '"');
            }
        }

        parent::__construct($elements);
    }
}
