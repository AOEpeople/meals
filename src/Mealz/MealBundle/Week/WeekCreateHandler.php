<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Week;

use App\Mealz\MealBundle\Controller\Exceptions\InvalidMealNewWeekDataException;
use App\Mealz\MealBundle\Controller\Exceptions\WeekAlreadyExistsException;
use App\Mealz\MealBundle\Day\DayUpdateHandlerInterface;
use App\Mealz\MealBundle\Helper\Exceptions\PriceNotFoundException;
use App\Mealz\MealBundle\Service\WeekService;
use App\Mealz\MealBundle\Week\Exception\InvalidWeekIdException;
use App\Mealz\MealBundle\Week\Model\WeekId;
use App\Mealz\MealBundle\Week\Model\WeekNotification;
use DateTime;
use Exception;
use Override;
use Symfony\Component\HttpFoundation\Request;

final readonly class WeekCreateHandler implements WeekCreateHandlerInterface
{
    public function __construct(
        private WeekExistingValidatorInterface $weekExistsValidator,
        private DayUpdateHandlerInterface $dayUpdateHandler,
        private WeekPersisterInterface $weekPersister,
        private string $lockParticipationAt,
    ) {
    }

    /**
     * @throws InvalidMealNewWeekDataException
     * @throws PriceNotFoundException
     * @throws WeekAlreadyExistsException
     * @throws InvalidWeekIdException
     * @throws Exception
     */
    #[Override]
    public function handleAndGet(DateTime $date, Request $request): WeekId
    {
        $this->weekExistsValidator->validate($date);
        $week = WeekService::generateEmptyWeek($date, $this->lockParticipationAt);
        $data = json_decode($request->getContent(), true);
        if (false === isset($data) || false === isset($data['days']) || false === isset($data['enabled'])) {
            throw new InvalidMealNewWeekDataException('Request body contains invalid data.');
        }

        $days = $data['days'];
        $week->setEnabled($data['enabled']);
        $weekDays = $week->getDays();
        $dayIndex = 0;
        foreach ($days as $dayData) {
            $index = $dayIndex++;
            if (0 < $dayData['id'] && $dayData['date'] === $weekDays[$index]->getDateTime()) {
                throw new Exception('no new day');
            }

            $this->dayUpdateHandler->handle($dayData, $weekDays[$index], 2);
        }

        return $this->weekPersister->persist($week, new WeekNotification($data['notify'] ?? false));
    }
}
