<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service\Mocks;

use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Repository\EventRepositoryInterface;

final class EventRepositoryMock implements EventRepositoryInterface
{
    public mixed $inputId;
    public array $inputFindOneByCriteria = [];
    public array $inputFindByCriteria = [];
    public mixed $outputFind;
    public array $outputFindAll = [];
    public array $outputFindBy = [];
    public mixed $outputFindOneBy;
    public string $className = Event::class;

    public function find($id)
    {
        $this->inputId = $id;

        return $this->outputFind;
    }

    public function findAll()
    {
        return $this->outputFindAll;
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
    {
        $this->inputFindByCriteria[] = [
            'criteria' => $criteria,
            'orderBy' => $orderBy,
            'limit' => $limit,
            'offset' => $offset,
        ];

        return $this->outputFindBy;
    }

    public function findOneBy(array $criteria)
    {
        $this->inputFindOneByCriteria[] = $criteria;

        return $this->outputFindOneBy;
    }

    public function getClassName()
    {
        return $this->className;
    }
}
