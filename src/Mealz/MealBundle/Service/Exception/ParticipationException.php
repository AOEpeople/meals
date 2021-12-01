<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Exception;

use Exception;
use Throwable;

class ParticipationException extends Exception
{
    public const ERR_MEAL_NOT_BOOKABLE = 1;
    public const ERR_GUEST_REG_MEAL_NOT_FOUND = 2;

    private array $context;

    public function __construct($message = "", $code = 0, Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);

        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
