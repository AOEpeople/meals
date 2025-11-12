<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Account\Model;

use DateTimeImmutable;
use Override;
use Psr\Clock\ClockInterface;

/**
 * @codeCoverageIgnore
 */
final readonly class Clock implements ClockInterface
{
    private DateTimeImmutable $dateTime;

    public function __construct()
    {
        $this->dateTime = new DateTimeImmutable();
    }

    #[Override]
    public function now(): DateTimeImmutable
    {
        return $this->dateTime;
    }
}