<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Week;
use DateTime;

class WeekService
{
    public static function generateEmptyWeek(DateTime $dateTime, string $dateTimeModifier): Week
    {
        $week = new Week();
        $week->setYear(intval($dateTime->format('o')));
        $week->setCalendarWeek(intval($dateTime->format('W')));

        $days = $week->getDays();
        for ($i = 0; $i < 5; ++$i) {
            $dayDateTime = clone $week->getStartTime();
            $dayDateTime->modify('+' . $i . ' days');
            $dayDateTime->setTime(12, 00);
            $lockParticipationDT = clone $dayDateTime;
            $lockParticipationDT->modify($dateTimeModifier);

            $day = new Day();
            $day->setDateTime($dayDateTime);
            $day->setLockParticipationDateTime($lockParticipationDT);
            $day->setWeek($week);
            $days->add($day);
        }

        return $week;
    }
}
