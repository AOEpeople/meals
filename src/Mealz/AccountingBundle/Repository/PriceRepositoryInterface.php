<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Repository;

use App\Mealz\AccountingBundle\Entity\Price;
use Doctrine\Common\Collections\Collection;

interface PriceRepositoryInterface
{
    /**
     * @return Collection<int, Price>
     */
    public function findAll(): Collection;

    public function findByYear(int $year): ?Price;
}
