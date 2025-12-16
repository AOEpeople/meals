<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Controller\Exceptions\InvalidMealNewWeekDataException;
use App\Mealz\MealBundle\Controller\Exceptions\WeekAlreadyExistsException;
use App\Mealz\MealBundle\Week\WeekCreateHandlerInterface;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[IsGranted('ROLE_KITCHEN_STAFF')]
final class WeekCreateController extends BaseController
{
    public function __construct(
        private readonly WeekCreateHandlerInterface $weekCreateHandler,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(DateTime $date, Request $request): JsonResponse
    {
        try {
            $weekId = $this->weekCreateHandler->handleAndGet($date, $request);

            return new JsonResponse($weekId->value, Response::HTTP_OK);
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
}
