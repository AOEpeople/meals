<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service\Mocks;

use App\Mealz\AccountingBundle\Entity\Price;
use App\Mealz\AccountingBundle\Repository\PriceRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Override;

final class PriceRepositoryMock implements PriceRepositoryInterface
{
    public Collection $outputFindAll;
    public array $inputFindByYearInputs = [];
    public ?Price $outputFindByYear;

    #[Override]
    public function findAll(): Collection
    {
        return $this->outputFindAll;
    }

    #[Override]
    public function findByYear(int $year): ?Price
    {
        $this->inputFindByYearInputs[] = $year;

        return $this->outputFindByYear;
    }
}
