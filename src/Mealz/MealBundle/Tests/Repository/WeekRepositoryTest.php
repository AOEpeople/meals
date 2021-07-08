<?php

namespace Mealz\MealBundle\Tests\Repository;

use DateTime;
use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadParticipants;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;

use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\MealBundle\Tests\AbstractDatabaseTestCase;

class WeekRepositoryTest extends AbstractDatabaseTestCase
{
    /** @var WeekRepository */
    protected $weekRepository;

    public function setUp()
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
                new LoadWeeks(),
                new LoadDays(),
                new LoadCategories(),
                new LoadDishes(),
                new LoadMeals(),
                new LoadParticipants()
        ]);

        $this->weekRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Week');
    }

    /**
     * @test
     */
    public function getCurrentWeek()
    {
        $currentDate = new DateTime();
        $week = $this->weekRepository->getCurrentWeek();
        $this->assertEquals($week->getCalendarWeek(), $currentDate->format('W'));
    }

    /**
     * @dataProvider getTestDates
     * @test
     *
     * @param  string $date     Test date
     */
    public function getNextWeek($date)
    {
        $dateTime = new DateTime($date);
        $dateWeek = $dateTime->format('W');
        $week = $this->weekRepository->getNextWeek($dateTime);
        $this->assertEquals($week->getCalendarWeek(), $dateWeek + 1);
    }

    public function getTestDates()
    {
        return [
            ['2016-10-16'],
            ['2016-10-19'],
            ['2016-10-22'],
            ['2016-10-23']
        ];
    }
}
