<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Week;

use App\Mealz\MealBundle\Controller\Exceptions\InvalidMealNewWeekDataException;
use App\Mealz\MealBundle\Controller\Exceptions\WeekAlreadyExistsException;
use App\Mealz\MealBundle\Helper\Exceptions\PriceNotFoundException;
use App\Mealz\MealBundle\Week\Exception\InvalidWeekIdException;
use App\Mealz\MealBundle\Week\Model\WeekId;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Request;

interface WeekCreateHandlerInterface
{
    /**
     * @throws InvalidMealNewWeekDataException
     * @throws PriceNotFoundException
     * @throws WeekAlreadyExistsException
     * @throws InvalidWeekIdException
     * @throws Exception
     */
    public function handleAndGet(DateTime $date, Request $request): WeekId;
}
