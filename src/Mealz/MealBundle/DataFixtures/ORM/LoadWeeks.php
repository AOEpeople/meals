<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Mealz\MealBundle\Entity\Week;

/**
 * Load the Weeks
 */
class LoadWeeks extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture
     */
    private const ORDER_NUMBER = 2;

    protected int $counter = 0;

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        $weeks = $this->getCurrentTestWeeks();

        foreach ($weeks as $weekDataSet) {
            $week = new Week();
            $week->setYear($weekDataSet['year']);
            $week->setCalendarWeek($weekDataSet['calendarWeek']);
            $manager->persist($week);
            $this->addReference('week-'.$this->counter++, $week);
        }

        $manager->flush();
    }

    /**
     * get the Order of fixtures loading
     */
    public function getOrder(): int
    {
        // load as second
        return self::ORDER_NUMBER;
    }

    /**
     * Gets the current test weeks.
     *
     * They are meant to be used as dummy data on integration systems.
     */
    private function getCurrentTestWeeks(): array
    {
        $currentWeeks = [];
        $date = new DateTime('monday last week');
        $maxDate = new DateTime('+1 month');

        while ($date < $maxDate) {
            $year = $date->format('o');
            $week = $date->format('W');
            $currentWeeks[$year.'-'.$week] = ['year' => $year, 'calendarWeek' => $week];
            $date->modify('+1 week');
        }

        return $currentWeeks;
    }
}
