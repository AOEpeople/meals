<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Week;

use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Event\WeekUpdateEvent;
use App\Mealz\MealBundle\Week\Model\WeekId;
use App\Mealz\MealBundle\Week\Model\WeekNotification;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class WeekPersister implements WeekPersisterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Override]
    public function persist(Week $week, WeekNotification $weekNotification): WeekId
    {
        $this->entityManager->persist($week);
        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(new WeekUpdateEvent($week, $weekNotification->shouldNotify));

        return new WeekId($week->getId());
    }
}
