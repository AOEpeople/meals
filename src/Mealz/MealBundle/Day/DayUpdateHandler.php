<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Day;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Helper\Exceptions\PriceNotFoundException;
use App\Mealz\MealBundle\Helper\MealAdminHelper;
use App\Mealz\MealBundle\Service\DayService;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Override;

final readonly class DayUpdateHandler implements DayUpdateHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DayService $dayService,
        private MealAdminHelper $mealAdminHelper,
    ) {
    }

    /**
     * @throws PriceNotFoundException
     */
    #[Override]
    public function handle(array $dayData, Day $day, int $count): void
    {
        $this->updateDaySettings($day, $dayData);
        $this->updateEvents($day, $dayData['events'] ?? []);
        $this->updateMeals($day, $dayData['meals'], $count);
    }

    private function updateDaySettings(Day $day, array $dayData): void
    {
        if (null !== $dayData['enabled']) {
            $day->setEnabled($dayData['enabled']);
        }
        $this->setLockParticipationForDay($day, $dayData);
    }

    private function updateEvents(Day $day, array $newEvents): void
    {
        $existingEventIds = $this->getExistingEventIds($day);
        $newEventIds = array_filter(array_column($newEvents, 'eventId'));
        $this->addNewEvents($day, $newEvents, $existingEventIds);
        $this->removeOldEvents($day, $newEventIds);

        $this->entityManager->flush();
    }

    private function getExistingEventIds(Day $day): array
    {
        return array_filter(array_map(
            fn ($e) => $e instanceof EventParticipation ? $e->getEvent()->getId() : null,
            $day->getEvents()->toArray()
        ));
    }

    /**
     * @throws PriceNotFoundException
     * @throws Exception
     */
    private function updateMeals(Day $day, array $mealCollection, int $count): void
    {
        if ($count < count($mealCollection)) {
            throw new Exception('106: too many meals requested');
        }
        // when updating a day remove old meals
        if (3 === $count) {
            $this->dayService->removeUnusedMeals($day, $mealCollection);
        }

        foreach ($mealCollection as $mealArr) {
            $this->mealAdminHelper->handleMealArray($mealArr, $day);
        }
    }

    private function setLockParticipationForDay(Day $dayEntity, array $day): void
    {
        if (
            null !== $day['lockDate']
            && true === isset($day['lockDate']['date'])
            && true === isset($day['lockDate']['timezone'])
        ) {
            $newDateStr = str_replace(' ', 'T', $day['lockDate']['date']) . '+00:00';
            $newDate = DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $newDateStr, new DateTimeZone($day['lockDate']['timezone']));
            $dayEntity->setLockParticipationDateTime($newDate);
        }
    }

    private function addNewEvents(Day $day, array $newEvents, array $existingEventIds): void
    {
        foreach ($newEvents as $eventArr) {
            if (!in_array($eventArr['eventId'], $existingEventIds, true) && (null !== $eventArr['eventId'])) {
                $this->addEvent($eventArr, $day);
            }
        }
    }

    private function removeOldEvents(Day $dayEntity, array $newEventIds): void
    {
        foreach ($dayEntity->getEvents() as $existingEvent) {
            if (!in_array($existingEvent->getEvent()->getId(), $newEventIds, true)) {
                $dayEntity->removeEvent($existingEvent);
                $this->entityManager->remove($existingEvent);
            }
        }
    }

    private function addEvent(array $event, Day $dayEntity)
    {
        $eventEntity = $this->mealAdminHelper->findEvent($event['eventId']);
        $eventParticipation = new EventParticipation($dayEntity, $eventEntity);
        $dayEntity->addEvent($eventParticipation);
    }
}
