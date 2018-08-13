<?php

namespace Mealz\MealBundle\Tests\Controller;

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
use Mealz\UserBundle\Entity\Profile;
use Mealz\UserBundle\Entity\Role;

/**
 * Participant Controller Test
 *
 * @author Henry Vogt <henry.vogt@aoe.com>
 */
class ParticipantControllerTest extends AbstractControllerTestCase
{
    protected static $participantFirstName;
    protected static $participantLastName;
    protected static $guestParticipantFirstName;
    protected static $guestParticipantLastName;
    protected static $guestCompany;
    protected static $userFirstName;
    protected static $userLastName;
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

        $dateTime = new \DateTime();
        $dateTime->add(new \DateInterval('PT1H'));
        self::$meal = $this->getRecentMeal($dateTime);

        // Create profile for participant
        self::$participantFirstName = 'Max';
        self::$participantLastName = 'Mustermann' . $time;
        $participant = $this->createEmployeeProfileAndParticipation(
            self::$participantFirstName,
            self::$participantLastName,
            self::$meal
        );

        // Create profile for guest participant
        self::$guestParticipantFirstName = 'Jon';
        self::$guestParticipantLastName = 'Doe' . $time;
        self::$guestCompany = 'Company';
        $guestParticipant = $this->createGuestProfileAndParticipation(
            self::$guestParticipantFirstName,
            self::$guestParticipantLastName,
            self::$guestCompany,
            self::$meal
        );

        // Create profile for user (non participant)
        self::$userFirstName = 'Karl';
        self::$userLastName = 'Schmidt' . $time;
        $user = $this->createProfile(self::$userFirstName, self::$userLastName);
        $this->persistAndFlushAll([$user]);

        // Check that created profiles are persisted
        if (($this->getUserProfile($participant->getProfile()->getUsername()) instanceof Profile) === false) {
            $this->fail('Test participant not found.');
        }
        if (($this->getUserProfile($guestParticipant->getProfile()->getUsername()) instanceof Profile) === false) {
            $this->fail('Test guest not found.');
        }
        if (($this->getUserProfile($user->getUsername()) instanceof Profile) === false) {
            $this->fail('Test user not found.');
        }
    }

    /**
     * Tests the swap action (offering a meal) in the participant controller.
     * First case: A participant offers his meal on time.
     * @test
     */
    public function offeringOneMeal()
    {
        $userProfile = $this->getUserProfile();

        //find locked meal and make user a participant of that
        $lockedMealsArray = $this->getDoctrine()->getRepository('MealzMealBundle:Meal')->getLockedMeals();
        $lockedMeal = $lockedMealsArray[0];
        $lockedParticipant = $this->createParticipant($userProfile, $lockedMeal);

        $this->persistAndFlushAll([$lockedParticipant]);

        $this->loginAsDefaultClient($userProfile);
        $id = $lockedParticipant->getId();
        $this->client->request('GET', '/menu/meal/' . $id . '/swap');

        //verification by checking the database
        $offeringParticipant = $this->getDoctrine()->getRepository('MealzMealBundle:Participant')->find($id);
        $this->assertTrue($offeringParticipant->getOfferedAt() !== 0, 'offeredAt value not changed');
    }

    /**
     * Second case: A participant takes his offer back.
     * @test
     */
    public function takingOfferBack()
    {
        $userProfile = $this->getUserProfile();
        $lockedMealsArray = $this->getDoctrine()->getRepository('MealzMealBundle:Meal')->getLockedMeals();
        $lockedMeal = $lockedMealsArray[0];
        $lockedParticipant = $this->createParticipant($userProfile, $lockedMeal);
        $lockedParticipant->setOfferedAt(time());
        $id = $lockedParticipant->getId();
        $this->persistAndFlushAll([$lockedParticipant]);

        $this->loginAsDefaultClient($userProfile);
        $this->client->request('GET', '/menu/meal/' . $id . '/unswap');

        //verification by checking the database
        $unswappingParticipant = $this->getDoctrine()->getRepository('MealzMealBundle:Participant')->find($id);
        $this->assertTrue($unswappingParticipant->getOfferedAt() === 0, 'unswapping not working');
    }

    /**
     * Third case: A participant tries to offer his outdated meal.
     * @test
     */
    public function offeringOutdatedMeal()
    {
        $userProfile = $this->getUserProfile();

        $outdatedMealsArray = $this->getDoctrine()->getRepository('MealzMealBundle:Meal')->getOutdatedMeals();
        $outdatedMeal = $outdatedMealsArray[0];
        $outdatedParticipant = $this->createParticipant($userProfile, $outdatedMeal);
        $id = $outdatedParticipant->getId();

        $this->persistAndFlushAll([$outdatedParticipant]);

        $this->loginAsDefaultClient($userProfile);
        $this->client->request('GET', '/menu/meal/' . $id . '/swap');

        //verification by checking the database
        $notOfferingParticipant = $this->getDoctrine()->getRepository('MealzMealBundle:Participant')->find($id);
        $this->assertTrue($notOfferingParticipant->getOfferedAt() === 0, 'user still offered meal');
    }

    /**
     * Check that the created participants are displayed in the participation table for the current week
     * @test
     */
    public function checkParticipantInParticipationTable()
    {
        $crawler = $this->getCurrentWeekParticipations();
        $this->assertEquals(1, $crawler->filter('html:contains("' . self::$participantFirstName . '")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("' . self::$participantLastName . '")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("' . self::$guestParticipantFirstName . '")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("' . self::$guestParticipantLastName . '")')->count());
    }

    /**
     * Check that the guest participant is displayed with a (<company name>) suffix
     * @test
     */
    public function checkGuestSuffixInParticipationTable()
    {
        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.table-row')
            ->reduce(function ($node) {
                $participantName = self::$guestParticipantLastName . ', ' . self::$guestParticipantFirstName;
                if (stripos($node->text(), $participantName) === false) {
                    return false;
                }
            })
            ->first();
        $this->assertContains(self::$guestCompany, $crawler->text());
    }

    /**
     * Check that the amount of participations is correct
     * @test
     */
    public function checkParticipationAmount()
    {
        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.meal-count > span')
            ->first();
        $this->assertContains('2', $crawler->text());
    }

    /**
     * Check the weekdate is displayed correct
     * @test
     */
    public function checkWeekDate()
    {
        $currentWeek = $this->getCurrentWeek();
        $firstWeekDay = date_format($currentWeek->getDays()->first()->getDateTime(), 'd.m.');
        $lastWeekDay = date_format($currentWeek->getDays()->last()->getDateTime(), 'd.m.');

        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.week-date')
            ->first();
        $this->assertContains($firstWeekDay . '-' . $lastWeekDay, $crawler->text());
    }

    /**
     * Check that the first day of the week is displayed correct
     * @test
     */
    public function checkFirstWeekDay()
    {
        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.day')
            ->first();
        $this->assertContains('Monday', $crawler->text());
    }

    /**
     * Check that the first dish title is displayed
     * @test
     */
    public function checkFirstDishTitle()
    {
        $currentWeek = $this->getCurrentWeek();
        $weekMeals = $currentWeek->getDays()->first()->getMeals();
        $firstWeekDish = $weekMeals->first()->getDish();
        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.meal-title')
            ->first();
        $this->assertContains($firstWeekDish->getTitle(), $crawler->text());
    }

    /**
     * Check that variations and parent-dish titles are displayed
     * @test
     */
    public function checkFirstVariationAndParentTitle()
    {
        $currentWeek = $this->getCurrentWeek();
        $weekMeals = $currentWeek->getDays()->first()->getMeals();
        $firstVariation = $weekMeals[1]->getDish();
        $firstVariationParent = $firstVariation->getParent();
        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.meal-title')
            ->eq(1);
        $template = '<span><b>%s</b><br>%s</span>';
        $html = sprintf($template, $firstVariationParent->getTitle(), $firstVariation->getTitle());
        // preg_replace() deletes every whitespace after the first
        $this->assertEquals($html, preg_replace('~\\s{2,}~', '', trim($crawler->html())));
    }

    /**
     * Check that the table data prototype is present in the dom
     * @test
     */
    public function checkTableDataPrototype()
    {
        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.table-content');
        $tableRow = '<tr class="table-row"><td class="text table-data wide-cell">__name__<\/td>';
        $tableData = '<td class="meal-participation table-data" data-attribute-action=".*__username__"><i class="glyphicon"><\/i><\/td>';
        $regex = '/(' . $tableRow . ')(' . $tableData . ')+(<\/tr>)/';
        $this->assertRegExp($regex, preg_replace("~\\s{2,}~", "", trim($crawler->attr("data-prototype"))));
    }

    /**
     * Check that the profiles list contains the non participating user
     * @test
     */
    public function checkProfileList()
    {
        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.profile-list');
        $userName = self::$userLastName . ', ' . self::$userFirstName;
        $this->assertContains($userName, $crawler->attr("data-attribute-profiles"));
    }

    /**
     * return crawler for the current week participations
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function getCurrentWeekParticipations()
    {
        $currentWeek = $this->getCurrentWeek();
        $crawler = $this->client->request('GET', '/participations/' . $currentWeek->getId() . '/edit');
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
