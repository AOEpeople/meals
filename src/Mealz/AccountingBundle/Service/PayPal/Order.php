<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service\PayPal;

use DateTime;
use DateTimeImmutable;

class Order
{
    /**
     * PayPal transaction code
     */
    private string $id;

    /**
     * Transaction gross amount
     */
    private float $amount;

    private DateTimeImmutable $dateTime;

    public function __construct(string $id, float $amount, DateTime $dateTime)
    {
        $this->id = $id;
        $this->amount = $amount;
        $this->dateTime = DateTimeImmutable::createFromMutable($dateTime);
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
}
