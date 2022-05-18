<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Logger;

use Psr\Log\LoggerInterface;
use Throwable;

interface MealsLoggerInterface extends LoggerInterface
{
    public function logException(Throwable $exc, string $message = '', array $context = []): void;
}
