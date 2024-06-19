<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Repository\WeekRepositoryInterface;
use DateTime;

class WeekService
{
    private WeekRepositoryInterface $weekRepo;

    public function __construct(WeekRepositoryInterface $weekRepo)
    {
        $this->weekRepo = $weekRepo;
    }

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

    private function createEmptyNonPersistentWeek(DateTime $dateTime): Week
    {
        $week = new Week();
        $week->setCalendarWeek((int) $dateTime->format('W'));
        $week->setYear((int) $dateTime->format('o'));

        return $week;
    }

    /**
     * @return Week[]
     *
     * @psalm-return array{0: Week, 1: Week}
     */
    public function getNextTwoWeeks(): array
    {
        $currentWeek = $this->weekRepo->getCurrentWeek();
        if (null === $currentWeek) {
            $currentWeek = $this->createEmptyNonPersistentWeek(new DateTime());
        }

        $nextWeek = $this->weekRepo->getNextWeek();
        if (null === $nextWeek) {
            $nextWeek = $this->createEmptyNonPersistentWeek(new DateTime('next week'));
        }

        return [$currentWeek, $nextWeek];
    }
}
