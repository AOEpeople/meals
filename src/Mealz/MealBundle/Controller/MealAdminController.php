<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Event\WeekUpdateEvent;
use App\Mealz\MealBundle\Helper\MealAdminHelper;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\DishRepositoryInterface;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Repository\WeekRepositoryInterface;
use App\Mealz\MealBundle\Service\DayService;
use App\Mealz\MealBundle\Service\DishService;
use App\Mealz\MealBundle\Service\WeekService;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;

#[IsGranted('ROLE_KITCHEN_STAFF')]
final class MealAdminController extends BaseController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly WeekRepositoryInterface $weekRepository,
        private readonly DishRepositoryInterface $dishRepository,
        private readonly MealRepositoryInterface $mealRepository,
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
        $week = $this->weekRepository->findOneBy([
            'year' => $date->format('o'),
            'calendarWeek' => $date->format('W'),
        ]);

        if (null !== $week) {
            return new JsonResponse(['message' => '102: week already exists'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $dateTimeModifier = $this->getParameter('mealz.lock_toggle_participation_at');
        $week = WeekService::generateEmptyWeek($date, $dateTimeModifier);

        $data = json_decode($request->getContent(), true);
        if (false === isset($data) || false === isset($data['days']) || false === isset($data['enabled'])) {
            return new JsonResponse(['message' => '101: invalid json'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $days = $data['days'];
        $week->setEnabled($data['enabled']);
        $weekDays = $week->getDays();

        try {
            $dayIndex = 0;
            foreach ($days as $dayData) {
                $this->handleNewDay($dayData, $weekDays[$dayIndex++]);
            }
        } catch (Exception $e) {
            return new JsonResponse(['message' => 'NoErrorNumber: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->em->persist($week);
        $this->em->flush();
        $this->eventDispatcher->dispatch(new WeekUpdateEvent($week, $data['notify']));

        return new JsonResponse($week->getId(), Response::HTTP_OK);
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
        $data = json_decode($request->getContent(), true);

        if (
            false === isset($data)
            || false === isset($data['days'])
            || false === isset($data['id'])
            || $data['id'] !== $week->getId()
            || false === isset($data['enabled'])
        ) {
            return new JsonResponse(['message' => '101: invalid json'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $days = $data['days'];
        $week->setEnabled($data['enabled']);
        try {
            foreach ($days as $day) {
                $this->handleDay($day);
            }
        } catch (Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->em->persist($week);
        $this->em->flush();
        $this->eventDispatcher->dispatch(new WeekUpdateEvent($week, $data['notify']));

        return new JsonResponse(null, Response::HTTP_OK);
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

    private function handleDay(array $day): void
    {
        $this->logger->info('handleDay');
        // check if day exists
        $dayEntity = $this->dayRepository->find($day['id']);
        if (null === $dayEntity) {
            throw new Exception('105: day not found');
        }

        if (null !== $day['enabled']) {
            $dayEntity->setEnabled($day['enabled']);
        }
        $this->setLockParticipationForDay($dayEntity, $day);

        $eventCollection = $day['events'];
        foreach ($eventCollection as $event) {
            $this->logger->info('Event '. implode($event));
            $this->handleEventArr($event, $dayEntity);
        }

        $mealCollection = $day['meals'];
        /*
         * 3 Meals are comprised of 2 main meals and a potential combined meal.
         * The combined meal is also in the collection, because meals that are
         * not in the collection get removed.
         */
        if (3 < count($mealCollection)) {
            throw new Exception('106: too many meals requested');
        }

        if (false === $this->dayService->checkMealsUpdatable($dayEntity, $mealCollection)) {
            throw new Exception('108: Meals must not have participations when being updated');
        }

        $this->dayService->removeUnusedMeals($dayEntity, $mealCollection);

        // parentMeal is an array of either one meal without variations or 1-2 variations
        foreach ($mealCollection as $mealArr) {
            $this->handleMealArray($mealArr, $dayEntity);
        }
    }

    private function handleNewDay($dayData, Day $day): void
    {
        // check for negative id
        if (0 < $dayData['id'] && $dayData['date'] === $day->getDateTime()) {
            throw new Exception('no new day');
        }

        if (null !== $dayData['enabled']) {
            $day->setEnabled($dayData['enabled']);
        }

        $this->setLockParticipationForDay($day, $dayData);

        $eventCollection = $dayData['events'];
        foreach($eventCollection as $eventArr){
            $this->handleEventArr($eventArr, $day);
        }
        $mealCollection = $dayData['meals'];
        // max 2 main meals allowed
        if (2 < count($mealCollection)) {
            throw new Exception('106: too many meals requested');
        }

        // parentMeal is an array of either one meal without variations or 1-2 variations
        foreach ($mealCollection as $mealArr) {
            $this->handleMealArray($mealArr, $day);
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

    private function handleMealArray(array $mealArr, Day $dayEntity): void
    {
        foreach ($mealArr as $meal) {
            if (false === isset($meal['dishSlug'])) {
                continue;
            }
            $dishEntity = $this->dishRepository->findOneBy(['slug' => $meal['dishSlug']]);
            if (null === $dishEntity) {
                throw new Exception('107: dish not found for slug: ' . $meal['dishSlug']);
            }
            // if mealId is null create meal
            if (false === isset($meal['mealId'])) {
                $this->createMeal($dishEntity, $dayEntity, $meal);
            } else {
                $this->modifyMeal($meal, $dishEntity, $dayEntity);
            }
        }
    }
    private function handleEventArr(array $eventArr, Day $day): void{
                    $this->addEvent($eventArr, $day);
            }

    private function addEvent(array $event, Day $dayEntity){
        $this->logger->info('EventId: '. $event['eventId']. ', name: '. $event['eventSlug']);
        $eventEntity = $this->mealAdminHelper->findEvent($event['eventId']);
        $eventExistsForDayAlready = $this->mealAdminHelper->checkIfEventExistsForDay($event['eventId'], $dayEntity);
        if(!$eventExistsForDayAlready){
            $this->logger->info('addEvent');
            $eventParticipationEntity = new EventParticipation($dayEntity, $eventEntity);
            $dayEntity->addEvent($eventParticipationEntity);
        }
    }

    private function createMeal(Dish $dishEntity, Day $dayEntity, array $meal): void
    {
        $mealEntity = new Meal($dishEntity, $dayEntity);
        $mealEntity->setPrice($dishEntity->getPrice());
        $this->mealAdminHelper->setParticipationLimit($mealEntity, $meal);
        $dayEntity->addMeal($mealEntity);
    }

    /**
     * @throws Exception
     */
    private function modifyMeal(array $meal, Dish $dishEntity, Day $dayEntity): void
    {
        $mealEntity = $this->mealRepository->find($meal['mealId']);
        if (null === $mealEntity) {
            // this happens because meals without participations are deleted, even though they
            // could be modified later on (this shouldn't happen but might)
            $mealEntity = new Meal($dishEntity, $dayEntity);
            $mealEntity->setPrice($dishEntity->getPrice());
            $dayEntity->addMeal($mealEntity);

            return;
        }

        $this->mealAdminHelper->setParticipationLimit($mealEntity, $meal);
        // check if the requested dish is the same as before
        if ($mealEntity->getDish()->getId() === $dishEntity->getId()) {
            return;
        }

        // check if meal already exists and can be modified (aka has no participations)
        if (!$mealEntity->hasParticipations()) {
            $mealEntity->setDish($dishEntity);
            $mealEntity->setPrice($dishEntity->getPrice());

            return;
        }

        throw new Exception('108: meal has participations for id: ' . $meal['mealId']);
    }
}
