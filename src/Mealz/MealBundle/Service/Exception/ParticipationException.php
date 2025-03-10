<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Exception;

use Exception;
use Throwable;

final class ParticipationException extends Exception
{
    public const ERR_MEAL_NOT_BOOKABLE = 1;
    public const ERR_GUEST_REG_MEAL_NOT_FOUND = 2;
    public const ERR_COMBI_MEAL_INVALID_DISH_COUNT = 3;
    public const ERR_INVALID_OPERATION = 5;
    public const ERR_PARTICIPATION_EXPIRED = 6;
    public const ERR_UPDATE_LOCKED_PARTICIPATION = 7;

    private array $context;

    public function __construct($message = '', $code = 0, ?Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);

        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function addContext(array $context): void
    {
        $this->context = array_merge($this->context, $context);
    }
}
