<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Controller\Exceptions\InvalidMealEditDataException;
use App\Mealz\MealBundle\Controller\Exceptions\InvalidMealNewWeekDataException;
use App\Mealz\MealBundle\Controller\Exceptions\WeekAlreadyExistsException;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Event\WeekUpdateEvent;
use App\Mealz\MealBundle\Helper\Exceptions\PriceNotFoundException;
use App\Mealz\MealBundle\Helper\MealAdminHelper;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\WeekRepositoryInterface;
use App\Mealz\MealBundle\Service\DayService;
use App\Mealz\MealBundle\Service\DishService;
use App\Mealz\MealBundle\Service\WeekService;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[IsGranted('ROLE_KITCHEN_STAFF')]
final class MealAdminController extends BaseController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly WeekRepositoryInterface $weekRepository,
        private readonly DayRepositoryInterface $dayRepository,
        private readonly DayService $dayService,
        private readonly DishService $dishService,
        private readonly EntityManagerInterface $em,
        private readonly MealAdminHelper $mealAdminHelper,
        private readonly LoggerInterface $logger
    ) {
    }

    public function getWeeks(): JsonResponse
    {
        $weeks = [];
        $dateTime = new DateTime();

        for ($i = 0; $i < 8; ++$i) {
            $modifiedDateTime = clone $dateTime;
            $modifiedDateTime->modify('+' . $i . ' weeks');
            $week = $this->weekRepository->findOneBy(
                [
                    'year' => $modifiedDateTime->format('o'),
                    'calendarWeek' => $modifiedDateTime->format('W'),
                ]
            );

            if (null === $week) {
                $week = new Week();
                $week->setYear($modifiedDateTime->format('o'));
                $week->setCalendarWeek($modifiedDateTime->format('W'));
            }

            $weeks[] = $week;
        }

        return new JsonResponse($weeks, Response::HTTP_OK);
    }

    public function new(DateTime $date, Request $request): JsonResponse
    {
        try {
            $week = $this->weekRepository->findOneBy([
                'year' => $date->format('o'),
                'calendarWeek' => $date->format('W'),
            ]);
            if (null !== $week) {
                throw new WeekAlreadyExistsException('Week already exists.');
            }

            $dateTimeModifier = $this->getParameter('mealz.lock_toggle_participation_at');
            $week = WeekService::generateEmptyWeek($date, $dateTimeModifier);
            $data = json_decode($request->getContent(), true);
            if (false === isset($data) || false === isset($data['days']) || false === isset($data['enabled'])) {
                throw new InvalidMealNewWeekDataException('Request body contains invalid data.');
            }

            $days = $data['days'];
            $week->setEnabled($data['enabled']);
            $weekDays = $week->getDays();
            $dayIndex = 0;
            foreach ($days as $dayData) {
                $this->handleNewDay($dayData, $weekDays[$dayIndex++]);
            }
            $this->em->persist($week);
            $this->em->flush();
            $this->eventDispatcher->dispatch(new WeekUpdateEvent($week, $data['notify']));

            return new JsonResponse($week->getId(), Response::HTTP_OK);
        } catch (InvalidMealNewWeekDataException) {
            $this->logger->error('New Meals week data is invalid.');

            return new JsonResponse(['message' => '101: invalid json'], Response::HTTP_BAD_REQUEST);
        } catch (WeekAlreadyExistsException) {
            $this->logger->error('Week data is already existing.', [
                'year' => $date->format('o'),
                'calendarWeek' => $date->format('W'),
            ]);

            return new JsonResponse(['message' => '102: week already exists'], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Throwable $exception) {
            $this->logger->error('New Week could not be created.', [
                'exceptionMessage' => $exception->getMessage()
            ]);

            return new JsonResponse(['message' => 'NoErrorNumber: ' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getEmptyWeek(DateTime $date): JsonResponse
    {
        $week = $this->weekRepository->findOneBy([
            'year' => $date->format('o'),
            'calendarWeek' => $date->format('W'),
        ]);

        if (null !== $week) {
            return new JsonResponse(['message' => '103: week already exists'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $dateTimeModifier = $this->getParameter('mealz.lock_toggle_participation_at');
        if (is_string($dateTimeModifier)) {
            $week = WeekService::generateEmptyWeek($date, $dateTimeModifier);

            return new JsonResponse($week, Response::HTTP_OK);
        }

        return new JsonResponse(['message' => '104: Error on generating empty week'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function edit(Request $request, Week $week): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (
                false === isset($data)
                || false === isset($data['days'])
                || false === isset($data['id'])
                || $data['id'] !== $week->getId()
                || false === isset($data['enabled'])
            ) {
                throw new InvalidMealEditDataException('Request body is invalid.');
            }

            $days = $data['days'];
            $week->setEnabled($data['enabled']);
            foreach ($days as $day) {
                $this->handleDay($day);
            }
            $this->em->persist($week);
            $this->em->flush();
            $this->eventDispatcher->dispatch(new WeekUpdateEvent($week, $data['notify']));

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (InvalidMealEditDataException) {
            $this->logger->error('Request body by /api/menu/{id} is invalid.');

            return new JsonResponse(['message' => '101: invalid json'], Response::HTTP_BAD_REQUEST);
        } catch (Throwable $exception) {
            $this->logger->error('Meals could not be edited in admin section.', [
                'exceptionMessage' => $exception->getMessage()
            ]);

            return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Returns a list of dish ids and how often they were taken in the last month.
     */
    public function count(): JsonResponse
    {
        $dishCount = [];
        try {
            $dishCount = $this->dishService->getDishCount();
        } catch (Exception $e) {
            return new JsonResponse(['message' => $e->getMessage(), 500]);
        }

        return new JsonResponse($dishCount, Response::HTTP_OK);
    }

    public function getLockDateTimeForWeek(Week $week): JsonResponse
    {
        $dateTimeModifier = $this->getParameter('mealz.lock_toggle_participation_at');
        $response = [];

        /** @var Day $day */
        foreach ($week->getDays() as $day) {
            $response[(string) $day->getId()] = $day->getDateTime()->modify((string) $dateTimeModifier);
        }

        return new JsonResponse($response, Response::HTTP_OK);
    }

    /**
     * @throws PriceNotFoundException
     * @throws Exception
     */
    private function handleDay(array $day): void
    {
        $dayEntity = $this->getDayEntity($day['id']);
        $this->updateDaySettings($dayEntity, $day);
        $this->updateEvents($dayEntity, $day['events'] ?? []);
        $this->updateMeals($dayEntity, $day['meals'], 3);
    }

    /**
     * @throws PriceNotFoundException
     * @throws Exception
     */
    private function handleNewDay($dayData, Day $day): void
    {
        if (0 < $dayData['id'] && $dayData['date'] === $day->getDateTime()) {
            throw new Exception('no new day');
        }

        $this->updateDaySettings($day, $dayData);
        $this->updateEvents($day, $dayData['events']);
        $this->updateMeals($day, $dayData['meals'], 2);
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

        $this->em->flush();
    }

    private function getExistingEventIds(Day $day): array
    {
        return array_filter(array_map(
            fn ($e) => $e instanceof EventParticipation ? $e->getEvent()->getId() : null,
            $day->getEvents()->toArray()
        ));
    }

    private function addNewEvents(Day $day, array $newEvents, array $existingEventIds): void
    {
        foreach ($newEvents as $eventArr) {
            if (!in_array($eventArr['eventId'], $existingEventIds, true) && (null !== $eventArr['eventId'])) {
                $this->addEvent($eventArr, $day);
            }
        }
    }

    private function getDayEntity(int $dayId): Day
    {
        $dayEntity = $this->dayRepository->find($dayId);
        if (null === $dayEntity) {
            throw new Exception('105: day not found');
        }

        return $dayEntity;
    }

    private function removeOldEvents(Day $dayEntity, array $newEventIds): void
    {
        foreach ($dayEntity->getEvents() as $existingEvent) {
            if (!in_array($existingEvent->getEvent()->getId(), $newEventIds, true)) {
                $dayEntity->removeEvent($existingEvent);
                $this->em->remove($existingEvent);
            }
        }
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

    private function addEvent(array $event, Day $dayEntity)
    {
        $eventEntity = $this->mealAdminHelper->findEvent($event['eventId']);
        $eventParticipation = new EventParticipation($dayEntity, $eventEntity);
        $dayEntity->addEvent($eventParticipation);
    }
}
