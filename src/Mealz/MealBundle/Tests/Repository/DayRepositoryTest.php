<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Repository;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Repository\DayRepository;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class DayRepositoryTest extends AbstractDatabaseTestCase
{
    private DayRepositoryInterface $dayRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadWeeks(),
            new LoadDays(),
        ]);

        $this->dayRepository = self::getContainer()->get(DayRepository::class);
    }

    /**
     * @test
     */
    public function getCurrentDay(): void
    {
        $currentDateTime = new DateTime();
        $day = $this->dayRepository->getCurrentDay();
        $this->assertNotNull($day);
        $this->assertEquals($currentDateTime->format('Y-m-d'), $day->getDateTime()->format('Y-m-d'));
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function getDayByDate(): void
    {
        $days = $this->dayRepository->findAll();
        $dayIdx = array_rand($days, 1);
        $day = $days[$dayIdx];

        $dayByDate = $this->dayRepository->getDayByDate($day->getDateTime());
        $this->assertNotNull($dayByDate);
        $this->assertEquals($day->getDateTime()->format('Y-m-d'), $dayByDate->getDateTime()->format('Y-m-d'));
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function noDayByDate(): void
    {
        $days = $this->dayRepository->findAll();
        $dayCollection = new ArrayCollection($days);
        $startTime = new DateTime('-100 days');
        $endTime = new DateTime('+100 days');

        $notFound = false;
        $dateTimeWithNoDay = new DateTime();
        while (!$notFound) {
            $randomTimestamp = random_int($startTime->getTimestamp(), $endTime->getTimestamp());
            $dateTimeWithNoDay->setTimestamp($randomTimestamp);
            $daysOnRandomDateTime = $dayCollection->filter(fn(Day $day
            ) => $dateTimeWithNoDay->format('Y-m-d') === $day->getDateTime()->format('Y-m-d'));
            $notFound = $daysOnRandomDateTime->isEmpty();
        }

        $dayByDate = $this->dayRepository->getDayByDate($dateTimeWithNoDay);
        $this->assertNull($dayByDate);
    }
}
