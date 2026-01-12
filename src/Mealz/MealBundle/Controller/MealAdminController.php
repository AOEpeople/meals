<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Controller\Exceptions\InvalidMealEditDataException;
use App\Mealz\MealBundle\Day\DayUpdateHandlerInterface;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Event\WeekUpdateEvent;
use App\Mealz\MealBundle\Helper\Exceptions\PriceNotFoundException;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\WeekRepositoryInterface;
use App\Mealz\MealBundle\Service\DishService;
use App\Mealz\MealBundle\Service\WeekService;
use DateTime;
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
        private readonly DishService $dishService,
        private readonly EntityManagerInterface $em,
        private readonly DayUpdateHandlerInterface $dayUpdateHandler,
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
                $week->setYear((int) $modifiedDateTime->format('o'));
                $week->setCalendarWeek((int) $modifiedDateTime->format('W'));
            }

            $weeks[] = $week;
        }

        return new JsonResponse($weeks, Response::HTTP_OK);
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
        $this->dayUpdateHandler->handle($day, $dayEntity, 3);
    }

    /**
     * @throws Exception
     */
    private function getDayEntity(int $dayId): Day
    {
        $dayEntity = $this->dayRepository->find($dayId);
        if (null === $dayEntity) {
            throw new Exception('105: day not found');
        }

        return $dayEntity;
    }
}
