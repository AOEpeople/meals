<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Event\WeekUpdateEvent;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class MealAdminController extends BaseController
{
    private EventDispatcherInterface $eventDispatcher;
    private WeekRepositoryInterface $weekRepository;
    private DishRepositoryInterface $dishRepository;
    private MealRepositoryInterface $mealRepository;
    private DayRepositoryInterface $dayRepository;
    private DayService $dayService;
    private DishService $dishService;
    private EntityManagerInterface $em;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        WeekRepositoryInterface $weekRepository,
        DishRepositoryInterface $dishRepository,
        MealRepositoryInterface $mealRepository,
        DayRepositoryInterface $dayRepository,
        DayService $dayService,
        DishService $dishService,
        EntityManagerInterface $em
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->weekRepository = $weekRepository;
        $this->dishRepository = $dishRepository;
        $this->mealRepository = $mealRepository;
        $this->dayRepository = $dayRepository;
        $this->dayService = $dayService;
        $this->dishService = $dishService;
        $this->em = $em;
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

        return new JsonResponse($weeks, 200);
    }

    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function new(DateTime $date): JsonResponse
    {
        $week = $this->weekRepository->findOneBy([
            'year' => $date->format('o'),
            'calendarWeek' => $date->format('W'),
        ]);

        if (null !== $week) {
            return new JsonResponse(['status' => 'week already exists'], 400);
        }

        $dateTimeModifier = $this->getParameter('mealz.lock_toggle_participation_at');
        $week = WeekService::generateEmptyWeek($date, $dateTimeModifier);

        $this->em->persist($week);
        $this->em->flush();

        return new JsonResponse(['status' => 'success'], 200);
    }

    public function edit(Request $request, Week $week): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data) || !isset($data['days']) || !isset($data['id']) || $data['id'] !== $week->getId() || !isset($data['enabled'])) {
            return new JsonResponse(['status' => 'invalid json'], 400);
        }
        $days = $data['days'];
        $week->setEnabled($data['enabled']);

        try {
            foreach ($days as $day) {
                $this->handleDay($day);
            }
        } catch (Exception $e) {
            return new JsonResponse(['status' => $e->getMessage()], 500);
        }

        $this->em->persist($week);
        $this->em->flush();
        $this->eventDispatcher->dispatch(new WeekUpdateEvent($week, $data['notify']));

        return new JsonResponse(['status' => 'success'], 200);
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
            return new JsonResponse(['status' => $e->getMessage(), 500]);
        }

        return new JsonResponse($dishCount, 200);
    }

    private function setParticipationLimit(Meal $mealEntity, $meal): void
    {
        if (isset($meal['participationLimit']) && 0 < $meal['participationLimit']) {
            $mealEntity->setParticipationLimit($meal['participationLimit']);
        } else {
            $mealEntity->setParticipationLimit(0);
        }
    }

    private function handleDay(array $day)
    {
        // check if day exists
        $dayEntity = $this->dayRepository->find($day['id']);
        if (null === $dayEntity) {
            throw new Exception('day not found');
        }

        if (null !== $day['enabled']) {
            $dayEntity->setEnabled($day['enabled']);
        }

        $this->setLockParticipationForDay($dayEntity, $day);

        $mealCollection = $day['meals'];
        // max 2 main meals allowed
        if (2 < count($mealCollection)) {
            throw new Exception('too many meals requested');
        }

        $this->dayService->removeUnusedMeals($dayEntity, $mealCollection);

        // parentMeal is an array of either one meal without variations or 1-2 variations
        foreach ($mealCollection as $mealArr) {
            $this->handleMealArray($mealArr, $dayEntity);
        }
    }

    private function setLockParticipationForDay(Day $dayEntity, array $day)
    {
        if (null !== $day['lockDate'] && isset($day['lockDate']['date']) && isset($day['lockDate']['timezone'])) {
            $newDateStr = str_replace(' ', 'T', $day['lockDate']['date']) . '+00:00';
            $newDate = DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $newDateStr, new DateTimeZone($day['lockDate']['timezone']));
            $dayEntity->setLockParticipationDateTime($newDate);
        }
    }

    private function handleMealArray(array $mealArr, Day $dayEntity)
    {
        foreach ($mealArr as $meal) {
            if (!isset($meal['dishSlug'])) {
                continue;
            }
            $dishEntity = $this->dishRepository->findOneBy(['slug' => $meal['dishSlug']]);
            if (null === $dishEntity) {
                throw new Exception('dish not found for slug: ' . $meal['dishSlug']);
            }
            // if mealId is null create meal
            if (!isset($meal['mealId'])) {
                $this->createMeal($dishEntity, $dayEntity, $meal);
            } else {
                $this->modifyMeal($meal, $dishEntity, $dayEntity);
            }
        }
    }

    private function createMeal(Dish $dishEntity, Day $dayEntity, array $meal)
    {
        $mealEntity = new Meal($dishEntity, $dayEntity);
        $this->setParticipationLimit($mealEntity, $meal);
        $dayEntity->addMeal($mealEntity);
    }

    private function modifyMeal(array $meal, Dish $dishEntity, Day $dayEntity)
    {
        $mealEntity = $this->mealRepository->find($meal['mealId']);

        // check if meal already exists and can be modified (aka has no participations)
        if (null !== $mealEntity && !$mealEntity->hasParticipations()) {
            $mealEntity->setDish($dishEntity);
            $this->setParticipationLimit($mealEntity, $meal);
        } elseif (null === $mealEntity) {
            // this happens because meals without participations are deleted, even though they could be modified later on (this shouldn't happen but might)
            $mealEntity = new Meal($dishEntity, $dayEntity);
            $this->setParticipationLimit($mealEntity, $meal);
            $dayEntity->addMeal($mealEntity);
        } else {
            throw new Exception('meal has participations for id: ' . $meal['mealId']);
        }
    }
}
