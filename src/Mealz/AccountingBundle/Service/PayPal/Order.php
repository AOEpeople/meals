<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service\PayPal;

use DateTime;
use DateTimeImmutable;

final class Order
{
    /**
     * PayPal Order-ID.
     */
    private string $id;

    /**
     * Transaction gross amount.
     */
    private float $amount;

    private DateTimeImmutable $dateTime;

    private string $status;

    public function __construct(string $id, float $amount, DateTime $dateTime, string $status)
    {
        $this->id = $id;
        $this->amount = $amount;
        $this->dateTime = DateTimeImmutable::createFromMutable($dateTime);
        $this->status = $status;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getDateTime(): DateTime
    {
        return DateTime::createFromImmutable($this->dateTime);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isCompleted(): bool
    {
        return 'COMPLETED' === $this->status;
    }
}
