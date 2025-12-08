<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Override;

#[ORM\Entity(repositoryClass: 'App\Mealz\AccountingBundle\Repository\PriceRepository')]
#[ORM\Table(name: 'price')]
class Price implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(name: 'year', type: 'integer', nullable: false)]
    private int $year;

    #[ORM\Column(name: 'price', type: 'decimal', precision: 10, scale: 2, nullable: false)]
    private float $price;

    #[ORM\Column(name: 'price_combined', type: 'decimal', precision: 10, scale: 2, nullable: false)]
    private float $priceCombined;

    public static function create(int $year, float $price, float $priceCombined): Price
    {
        $newPrice = new self();
        $newPrice->setYear($year);
        $newPrice->setPrice($price);
        $newPrice->setPriceCombined($priceCombined);

        return $newPrice;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPriceCombined(float $priceCombined): void
    {
        $this->priceCombined = $priceCombined;
    }

    public function getPriceCombined(): float
    {
        return $this->priceCombined;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'year' => $this->getYear(),
            'price' => $this->getPrice(),
            'price_combined' => $this->getPriceCombined(),
        ];
    }
}
