<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Account\Model;

use DateTimeImmutable;
use Override;
use Psr\Clock\ClockInterface;

/**
 * @codeCoverageIgnore
 */
final readonly class MutableClock implements ClockInterface
{
    public function __construct(
        private string $dateTime
    ) {
    }

    #[Override]
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->dateTime);
    }
}
