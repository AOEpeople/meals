<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Entity;

use App\Mealz\MealBundle\Entity\Week;
use DateTime;
use PHPUnit\Framework\TestCase;

final class WeekTest extends TestCase
{
    /**
     * @testdox Week starts on Monday at 00:00:00.
     */
    public function testGetStartTime(): void
    {
        $now = new DateTime();
        $calWeek = (int) $now->format('W');
        $calYear = (int) $now->format('Y');

        $week = new Week();
        $week->setCalendarWeek($calWeek);
        $week->setYear($calYear);

        $expectedTime = new DateTime('monday this week');

        $weekStartTime = $week->getStartTime();
        $this->assertEquals($expectedTime, $weekStartTime);
        $this->assertSame('00:00:00', $weekStartTime->format('H:i:s'));
    }

    /**
     * @testdox Week ends on Friday at 23:59:59.
     */
    public function testGetEndTime(): void
    {
        $now = new DateTime();
        $calWeek = (int) $now->format('W');
        $calYear = (int) $now->format('Y');

        $week = new Week();
        $week->setCalendarWeek($calWeek);
        $week->setYear($calYear);

        $expectedTime = new DateTime('friday this week 23:59:59');

        $weekEndTime = $week->getEndTime();
        $this->assertEquals($expectedTime, $weekEndTime);
        $this->assertSame('23:59:59', $weekEndTime->format('H:i:s'));
    }
}
