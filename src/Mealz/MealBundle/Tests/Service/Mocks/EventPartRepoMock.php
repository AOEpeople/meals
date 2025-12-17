<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service\Mocks;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Repository\EventPartRepoInterface;
use Override;

final class EventPartRepoMock implements EventPartRepoInterface
{
    public array $added = [];
    public array $findInputs = [];
    public mixed $outputFind;
    public array $outputFindAll = [];
    public array $outputFindBy = [];
    public mixed $outputFindOneBy;
    public array $findByCalls = [];
    public array $findOneByCriteria = [];
    public ?EventParticipation $outputFindByEventAndDay;
    public ?Day $findByEventAndDayDayInput;
    public ?Event $findByEventAndDayEventInput;
    public ?Day $findByEventIdAndDayDayInput;
    public int $findByEventIdAndDayEventIdInput;
    public EventParticipation $outputFindByEventIdAndDay;
    public string $className = EventParticipation::class;

    #[Override]
    public function add($eventParticipation): void
    {
        $this->added[] = $eventParticipation;
    }

    #[Override]
    public function findByEventAndDay(Day $day, Event $event): ?EventParticipation
    {
        $this->findByEventAndDayDayInput = $day;
        $this->findByEventAndDayEventInput = $event;

        return $this->outputFindByEventAndDay;
    }

    #[Override]
    public function find($id)
    {
        $this->findInputs[] = $id;

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
        $this->findByCalls[] = [
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
        $this->findOneByCriteria[] = $criteria;

        return $this->outputFindOneBy;
    }

    #[Override]
    public function findByEventIdAndDay(Day $day, int $eventId): ?EventParticipation
    {
        $this->findByEventIdAndDayDayInput = $day;
        $this->findByEventIdAndDayEventIdInput = $eventId;

        return $this->outputFindByEventIdAndDay;
    }

    #[Override]
    public function getClassName()
    {
        return $this->className;
    }
}
