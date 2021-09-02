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
        $weeks = array_merge($weeks, $this->getStaticTestWeeks());

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
        $date = new DateTime('12:00');
        $maxDate = new DateTime('+1 month');

        while ($date < $maxDate) {
            $year = $date->format('o');
            $week = $date->format('W');
            $currentWeeks[$year.'-'.$week] = ['year' => $year, 'calendarWeek' => $week];
            $date->modify('+1 week');
        }

        return $currentWeeks;
    }

    /**
     * Gets the static test weeks.
     *
     * They are meant to be used with unit or functional tests.
     */
    private function getStaticTestWeeks(): array
    {
        return [
            '2016-41' => ['year' => '2016', 'calendarWeek' => '41'],
            '2016-42' => ['year' => '2016', 'calendarWeek' => '42'],
            '2016-43' => ['year' => '2016', 'calendarWeek' => '43'],
            '2016-44' => ['year' => '2016', 'calendarWeek' => '44'],
            '2016-45' => ['year' => '2016', 'calendarWeek' => '45'],
            '2016-46' => ['year' => '2016', 'calendarWeek' => '46'],
        ];
    }
}
