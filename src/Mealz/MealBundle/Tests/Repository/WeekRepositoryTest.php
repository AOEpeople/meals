<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Repository;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Repository\WeekRepositoryInterface;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use DateTime;
use DateTimeImmutable;

class WeekRepositoryTest extends AbstractDatabaseTestCase
{
    protected WeekRepositoryInterface $weekRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([new LoadWeeks()]);

        $this->weekRepository = self::getContainer()->get(WeekRepositoryInterface::class);
    }

    public function testGetGetCurrentWeek(): void
    {
        $now = new DateTime();
        $currentWeek = $this->weekRepository->getCurrentWeek();
        $this->assertSame((int)$now->format('W'), $currentWeek->getCalendarWeek());
    }

    public function testGetNextWeek(): void
    {
        $now = new DateTimeImmutable();
        $currCalWeek = (int)$now->format('W');

        $lastCalWeek = (int)$now->modify('last day of December')->format('W');
        $this->assertContains($lastCalWeek, [52, 53, 1]);

        $nextWeek = $this->weekRepository->getNextWeek(DateTime::createFromImmutable($now));
        $this->assertInstanceOf(Week::class, $nextWeek);

        $nextCalWeek = $nextWeek->getCalendarWeek();

        if ($currCalWeek === $lastCalWeek) {
            $this->assertSame(1, $nextCalWeek);
        } else {
            $this->assertSame($currCalWeek + 1, $nextCalWeek);
        }
    }
}
