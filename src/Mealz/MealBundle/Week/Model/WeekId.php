<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Week\Model;

use App\Mealz\MealBundle\Week\Exception\InvalidWeekIdException;

/**
 * @codeCoverageIgnore
 */
final readonly class WeekId
{
    /**
     * @throws InvalidWeekIdException
     */
    public function __construct(
        public ?int $value
    ) {
        if (is_null($this->value)) {
            throw new InvalidWeekIdException('Week id cannot be null.');
        }
    }
}
