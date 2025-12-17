<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service\Mocks;

use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Repository\EventRepositoryInterface;
use Override;

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

    #[Override]
    public function find($id)
    {
        $this->inputId = $id;

        return $this->outputFind;
    }

    #[Override]
    public function findAll()
    {
        return $this->outputFindAll;
    }

    #[Override]
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

    #[Override]
    public function findOneBy(array $criteria)
    {
        $this->inputFindOneByCriteria[] = $criteria;

        return $this->outputFindOneBy;
    }

    #[Override]
    public function getClassName()
    {
        return $this->className;
    }
}
