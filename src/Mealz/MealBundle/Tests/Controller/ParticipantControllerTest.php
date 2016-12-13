<?php

namespace Mealz\MealBundle\Tests\Controller;

use Doctrine\Common\Collections\Criteria;
use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\Week;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Mealz\UserBundle\Entity\Role;

/**
 * Participant Controller Test
 *
 * @author Henry Vogt <henry.vogt@aoe.com>
 */
class ParticipantControllerTest extends AbstractControllerTestCase
{
    protected static $userFirstName;
    protected static $userLastName;
    protected static $guestFirstName;
    protected static $guestLastName;
    protected static $meal;

    /**
     * prepare test environment
     */
    public function setUp()
    {
        parent::setUp();

        $this->createAdminClient();
        $this->clearAllTables();
        $this->loadFixtures([
            new LoadCategories(),
            new LoadWeeks(),
            new LoadDays(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadMeals(),
            new LoadRoles(),
            new LoadUsers($this->client->getContainer()),
        ]);

        $time = time();

        self::$meal = $this->getRecentMeal();

        // Create profile for user
        self::$userFirstName = 'Max';
        self::$userLastName  = 'Mustermann'.$time;
        $employee = $this->createEmployeeProfileAndParticipation(self::$userFirstName, self::$userLastName, self::$meal);

        // Create profile for guest
        self::$guestFirstName = 'Jon';
        self::$guestLastName  = 'Doe'.$time;
        $guestCompany   = 'Company';
        $guest = $this->createGuestProfileAndParticipation(self::$guestFirstName, self::$guestLastName, $guestCompany, self::$meal);

        // Check that created profiles are persisted
        if (!$this->getUserProfile($employee->getProfile()->getUsername())) {
            $this->fail('No test employee found.');
        }
        if (!$this->getUserProfile($guest->getProfile()->getUsername())) {
            $this->fail('No test guest found.');
        }
    }

    /**
     * @test
     */
    public function checkParticipantInParticipationTable()
    {
        $crawler = $this->getCurrentWeekParticipations();
        $this->assertEquals(1, $crawler->filter('html:contains("'.self::$userFirstName.'")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("'.self::$userLastName.'")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("'.self::$guestFirstName.'")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("'.self::$guestLastName.'")')->count());
    }

    /**
     * @test
     */
    public function checkGuestSuffixInParticipationTable()
    {
        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.table-row')
            ->reduce(function ($node) {
                if ($node->text(self::$guestFirstName.', '.self::$guestLastName)) {
                    return true;
                }
            })
            ->first()
        ;
        $this->assertContains('Gast', $crawler->text());
    }

    /**
     * @test
     */
    public function checkParticipationAmount()
    {
        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.meal-count > span')
            ->first()
        ;
        $this->assertContains('2', $crawler->text());
    }

    /**
     * @test
     */
    public function checkWeekDate()
    {
        $currentWeek = $this->getCurrentWeek();
        $firstWeekDay = date_format($currentWeek->getDays()->first()->getDateTime(), 'd.m.');
        $lastWeekDay = date_format($currentWeek->getDays()->last()->getDateTime(), 'd.m.');

        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.week-date')
            ->first()
        ;
        $this->assertContains($firstWeekDay.'-'.$lastWeekDay, $crawler->text());
    }

    /**
     * @test
     */
    public function checkFirstWeekDay()
    {
        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.day')
            ->first()
        ;
        $this->assertContains('Monday', $crawler->text());
    }

    /**
     * @test
     */
    public function checkFirstDishTitle()
    {
        $currentWeek = $this->getCurrentWeek();
        $weekMeals = $currentWeek->getDays()->first()->getMeals();
        $firstWeekDish = $weekMeals->first()->getDish();
        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.meal-title')
            ->first()
        ;
        $this->assertContains($firstWeekDish->getTitle(), $crawler->text());
    }

//    /**
//     * @test
//     */
//    public function checkParentAndVariationTitle()
//    {
//        $currentWeek = $this->getCurrentWeek();
//        $weekMeals = $currentWeek->getDays()->first()->getMeals();
//        $firstVariation = $weekMeals[4]->getDish();
//        $crawler = $this->getCurrentWeekParticipations()
//            ->filter('.meal-title')
//            ->first()
//        ;
//        $this->assertContains($firstWeekDish->getTitle(), $crawler->text());
//    }

//file_put_contents('/tmp/debug.log', $html."/n", FILE_APPEND);

    /**
     * return crawler for the current week participations
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function getCurrentWeekParticipations()
    {
        $currentWeek = $this->getCurrentWeek();
        $crawler = $this->client->request('GET', '/participations/'.$currentWeek->getId().'/edit');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        return $crawler;
    }

    /**
     * return current week object
     * @return Week
     */
    protected function getCurrentWeek()
    {
        /** @var WeekRepository $weekRepository */
        $weekRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Week');
        /** @var Week $currentWeek */
        $currentWeek = $weekRepository->getCurrentWeek();

        return $currentWeek;
    }

    /**
     * create profile for user and add participation
     * @param $userFirstName
     * @param $userLastName
     * @param $meal
     * @return Participant
     */
    protected function createEmployeeProfileAndParticipation($userFirstName, $userLastName, $meal)
    {
        $user = $this->createProfile($userFirstName, $userLastName);
        $this->persistAndFlushAll([$user]);
        $participant = new Participant();
        $participant->setProfile($user);
        $participant->setMeal($meal);

        $this->persistAndFlushAll([$participant]);

        return $participant;
    }

    /**
     * create profile for guest and add participation
     * @param $guestFirstName
     * @param $guestLastName
     * @param $guestCompany
     * @param $meal
     * @return Participant
     */
    protected function createGuestProfileAndParticipation($guestFirstName, $guestLastName, $guestCompany, $meal)
    {
        $user = $this->createProfile($guestFirstName, $guestLastName, $guestCompany);
        $user->addRole($this->getRole(Role::ROLE_GUEST));
        $this->persistAndFlushAll([$user]);
        $participant = new Participant();
        $participant->setProfile($user);
        $participant->setMeal($meal);

        $this->persistAndFlushAll([$participant]);

        return $participant;
    }
}
