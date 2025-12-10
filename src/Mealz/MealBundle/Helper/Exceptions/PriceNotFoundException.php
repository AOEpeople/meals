<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Helper\Exceptions;

use Exception;

/**
 * @codeCoverageIgnore
 */
final class PriceNotFoundException extends Exception
{
    public static function isNotFound(int $year): self
    {
        return new self(
            sprintf('Price not found for year "%d".', $year)
        );
    }
}
