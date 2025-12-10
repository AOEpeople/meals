<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service\Mocks;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use DateTime;

final class DayRepositoryMock implements DayRepositoryInterface
{
    public ?Day $outputGetCurrentDay = null;
    public ?Day $outputGetDayByDate = null;
    public ?DateTime $inputGetDayByDate = null;
    public array $outputFindAllActive = [];
    public ?DateTime $inputFindAllActiveStartDate = null;
    public ?DateTime $inputFindAllActiveEndDate = null;
    public mixed $outputFind = null;
    public mixed $inputFind = null;
    public array $outputFindAll = [];
    public array $outputFindBy = [];
    public array $inputFindByCriteria = [];
    public ?array $inputFindByOrderBy = null;
    public ?int $inputFindByLimit = null;
    public ?int $inputFindByOffset = null;
    public mixed $outputFindOneBy = null;
    public array $inputFindOneByCriteria = [];
    public string $outputGetClassName = self::class;

    public function getCurrentDay(): ?Day
    {
        return $this->outputGetCurrentDay;
    }

    public function getDayByDate(DateTime $dateTime): ?Day
    {
        $this->inputGetDayByDate = $dateTime;

        return $this->outputGetDayByDate;
    }

    public function findAllActive(DateTime $startDate, DateTime $endDate): array
    {
        $this->inputFindAllActiveStartDate = $startDate;
        $this->inputFindAllActiveEndDate = $endDate;

        return $this->outputFindAllActive;
    }

    public function find($id)
    {
        $this->inputFind = $id;

        return $this->outputFind;
    }

    public function findAll()
    {
        return $this->outputFindAll;
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
    {
        $this->inputFindByCriteria = $criteria;
        $this->inputFindByOrderBy = $orderBy;
        $this->inputFindByLimit = $limit;
        $this->inputFindByOffset = $offset;

        return $this->outputFindBy;
    }

    public function findOneBy(array $criteria)
    {
        $this->inputFindOneByCriteria = $criteria;

        return $this->outputFindOneBy;
    }

    public function getClassName()
    {
        return $this->outputGetClassName;
    }
}
