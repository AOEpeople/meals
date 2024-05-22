<?php

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Service\WeekService;
use DateTime;
use PHPUnit\Framework\TestCase;

class WeekServiceTest extends TestCase
{
    /**
     * @test
     */
    public function generateEmptyWeekOnMonday(): void
    {
        $startDateTime = new DateTime('2022-01-24 00:00:00');
        $monday = clone $startDateTime;
        $week = WeekService::generateEmptyWeek($monday, '-1 day 16:00');

        $endDateTime = new DateTime('2022-01-28 23:59:59');
        $this->checkWeek($week, $startDateTime, $endDateTime);
    }

    /**
     * @test
     */
    public function generateEmptyWeekOnFriday(): void
    {
        $endDateTime = new DateTime('2022-01-28 23:59:59');
        $friday = clone $endDateTime;
        $week = WeekService::generateEmptyWeek($friday, '-1 day 16:00');

        $startDateTime = new DateTime('2022-01-24 00:00:00');
        $this->checkWeek($week, $startDateTime, $endDateTime);
    }

    /**
     * @test
     *
     * TODO this behavior is unexpected, needs to be fixed. When you a generate week on a weekend and should be the upcoming not the past week!
     */
    public function generateEmptyWeekOnSaturdayBefore(): void
    {
        $saturdayBefore = new DateTime('2022-01-22 12:00:00');
        $week = WeekService::generateEmptyWeek($saturdayBefore, '-1 day 16:00');

        $startDateTime = new DateTime('2022-01-17 00:00:00'); // should be 2022-01-24 00:00:00
        $endDateTime = new DateTime('2022-01-21 23:59:59'); // should be 2022-01-28 23:59:59
        $this->checkWeek($week, $startDateTime, $endDateTime);
    }

    /**
     * @test
     *
     * TODO this behavior is unexpected, needs to be fixed. When you a generate week on a weekend and should be the upcoming not the past week!
     */
    public function generateEmptyWeekOnSundayBefore(): void
    {
        $saturdayBefore = new DateTime('2022-01-23 12:00:00');
        $week = WeekService::generateEmptyWeek($saturdayBefore, '-1 day 16:00');

        $startDateTime = new DateTime('2022-01-17 00:00:00'); // should be 2022-01-24 00:00:00
        $endDateTime = new DateTime('2022-01-21 23:59:59'); // should be 2022-01-28 23:59:59
        $this->checkWeek($week, $startDateTime, $endDateTime);
    }

    /**
     * @test
     *
     * TODO this behavior is unexpected, needs to be fixed. When you a generate week on a weekend and should be the upcoming not the past week!
     */
    public function generateEmptyWeekOnSaturdayAfter(): void
    {
        $saturdayBefore = new DateTime('2022-01-29 12:00:00');
        $week = WeekService::generateEmptyWeek($saturdayBefore, '-1 day 16:00');

        $startDateTime = new DateTime('2022-01-24 00:00:00'); // should be 2022-01-31 00:00:00
        $endDateTime = new DateTime('2022-01-28 23:59:59'); // should be 2022-02-04 23:59:59
        $this->checkWeek($week, $startDateTime, $endDateTime);
    }

    /**
     * @test
     *
     * TODO this behavior is unexpected, needs to be fixed. When you a generate week on a weekend and should be the upcoming not the past week!
     */
    public function generateEmptyWeekOnSundayAfter(): void
    {
        $saturdayBefore = new DateTime('2022-01-30 12:00:00');
        $week = WeekService::generateEmptyWeek($saturdayBefore, '-1 day 16:00');

        $startDateTime = new DateTime('2022-01-24 00:00:00'); // should be 2022-01-31 00:00:00
        $endDateTime = new DateTime('2022-01-28 23:59:59'); // should be 2022-02-04 23:59:59
        $this->checkWeek($week, $startDateTime, $endDateTime);
    }

    private function checkWeek(Week $week, DateTime $startDateTime, DateTime $endDateTime)
    {
        $this->assertEquals(intval($startDateTime->format('o')), $week->getYear());
        $this->assertEquals(intval($startDateTime->format('W')), $week->getCalendarWeek());
        $this->assertEquals($startDateTime->format('Y-m-d H:i:s'), $week->getStartTime()->format('Y-m-d H:i:s'));
        $this->assertEquals($endDateTime->format('Y-m-d H:i:s'), $week->getEndTime()->format('Y-m-d H:i:s'));
        $this->assertCount(5, $week->getDays());

        $i = 0;
        foreach ($week->getDays() as $day) {
            $lunchDateTime = clone $startDateTime;
            $lunchDateTime->modify('+' . $i . 'days');
            $lunchDateTime->setTime(12, 0);
            $this->assertEquals($lunchDateTime->format('Y-m-d H:i:s'), $day->getDateTime()->format('Y-m-d H:i:s'));
            ++$i;
        }
    }
}
