<?php

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Participant Controller Test
 *
 * @author Henry Vogt <henry.vogt@aoe.com>
 */
class ParticipantControllerTest extends AbstractControllerTestCase
{
    protected static $participantFirstName;
    protected static $participantLastName;
    protected static $guestFirstName;
    protected static $guestLastName;
    protected static $guestCompany;
    protected static $userFirstName;
    protected static $userLastName;
    protected static $meal;

    /**
     * prepare test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadCategories(),
            new LoadWeeks(),
            new LoadDays(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadMeals(),
            new LoadRoles(),
            // self::$container is a special container that allow access to private services
            // see: https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);

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
        self::$guestFirstName = 'Jon';
        self::$guestLastName = 'Doe' . $time;
        self::$guestCompany = 'Company';
        $guestParticipant = $this->createGuestProfileAndParticipation(
            self::$guestFirstName,
            self::$guestLastName,
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
    public function offeringOneMeal(): void
    {
        $userProfile = $this->getUserProfile(self::USER_STANDARD);

        //find locked meal and make user a participant of that
        $lockedMealsArray = $this->getLockedMeals();
        $lockedMeal = $lockedMealsArray[0];
        $lockedParticipant = $this->createParticipant($userProfile, $lockedMeal);

        $this->persistAndFlushAll([$lockedParticipant]);

        $this->loginAs(self::USER_STANDARD);
        $participantId = $lockedParticipant->getId();
        $this->client->request('GET', '/menu/meal/' . $participantId . '/swap');

        //verification by checking the database
        $offeringParticipant = $this->getDoctrine()->getRepository('MealzMealBundle:Participant')->find($participantId);
        $this->assertNotSame($offeringParticipant->getOfferedAt(), 0, 'offeredAt value not changed');
    }

    /**
     * Second case: A participant takes his offer back.
     * @test
     */
    public function takingOfferBack(): void
    {
        $userProfile = $this->getUserProfile(self::USER_STANDARD);
        $lockedMealsArray = $this->getLockedMeals();
        $lockedMeal = $lockedMealsArray[0];
        $lockedParticipant = $this->createParticipant($userProfile, $lockedMeal);
        $lockedParticipant->setOfferedAt(time());
        $participantId = $lockedParticipant->getId();
        $this->persistAndFlushAll([$lockedParticipant]);

        $this->loginAs(self::USER_STANDARD);
        $this->client->request('GET', '/menu/meal/' . $participantId . '/unswap');

        //verification by checking the database
        $participant = $this->getDoctrine()->getRepository('MealzMealBundle:Participant')->find($participantId);
        $this->assertSame($participant->getOfferedAt(), 0, 'failed to retain swapped meal');
    }

    /**
     * Third case: A participant tries to offer his outdated meal.
     * @test
     */
    public function offeringOutdatedMeal(): void
    {
        $userProfile = $this->getUserProfile(self::USER_STANDARD);

        $outdatedMealsArray = $this->getDoctrine()->getRepository('MealzMealBundle:Meal')->getOutdatedMeals();
        $outdatedMeal = $outdatedMealsArray[0];
        $outdatedParticipant = $this->createParticipant($userProfile, $outdatedMeal);
        $participantId = $outdatedParticipant->getId();

        $this->persistAndFlushAll([$outdatedParticipant]);

        $this->loginAs(self::USER_STANDARD);
        $this->client->request('GET', '/menu/meal/' . $participantId . '/swap');

        //verification by checking the database
        $notOfferingPart = $this->getDoctrine()->getRepository('MealzMealBundle:Participant')->find($participantId);
        $this->assertSame($notOfferingPart->getOfferedAt(), 0, 'user still offered meal');
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
        $this->assertEquals(1, $crawler->filter('html:contains("' . self::$guestFirstName . '")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("' . self::$guestLastName . '")')->count());
    }

    /**
     * Check that the guest participant is displayed with a (<company name>) suffix
     * @test
     */
    public function checkGuestSuffixInParticipationTable()
    {
        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.table-row')
            ->reduce(static function ($node) {
                $participantName = self::$guestLastName . ', ' . self::$guestFirstName;
                if (stripos($node->text(), $participantName) === false) {
                    return false;
                }
            })
            ->first();
        $this->assertStringContainsString(self::$guestCompany, $crawler->text());
    }

    /**
     * @test
     */
    public function checkParticipationCount(): void
    {
        $crawler = $this->getCurrentWeekParticipations();
        $dailyParticipationCount = $crawler->filter('.meal-count > span')->each(static function ($node, $i): string {
            return $node->text();
        });
        $this->assertContains('2', $dailyParticipationCount);
    }

    /**
     * Check the weekdate is displayed correct
     * @test
     */
    public function checkWeekDate(): void
    {
        $currentWeek = $this->getCurrentWeek();
        $firstWeekDay = date_format($currentWeek->getDays()->first()->getDateTime(), 'd.m.');
        $lastWeekDay = date_format($currentWeek->getDays()->last()->getDateTime(), 'd.m.');

        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.week-date')
            ->first();
        $this->assertStringContainsString($firstWeekDay . '-' . $lastWeekDay, $crawler->text());
    }

    /**
     * Check that the first day of the week is displayed correct
     * @test
     */
    public function checkFirstWeekDay(): void
    {
        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.day')
            ->first();
        $this->assertStringContainsString('Monday', $crawler->text());
    }

    /**
     * Check that the first dish title is displayed
     * @test
     */
    public function checkFirstDishTitle(): void
    {
        $currentWeek = $this->getCurrentWeek();
        $weekMeals = $currentWeek->getDays()->first()->getMeals();
        $firstWeekDish = $weekMeals->first()->getDish();
        $crawler = $this->getCurrentWeekParticipations()->filter('.meal-title')->first();

        $this->assertEquals($firstWeekDish->getTitle(), $crawler->text());
    }

    /**
     * Check that variations and parent-dish titles are displayed
     * @test
     */
    public function checkFirstVariationAndParentTitle(): void
    {
        $firstDishVariation = null;

        foreach ($this->getCurrentWeek()->getDays() as $day) {
            /** @var Day $day */
            foreach ($day->getMeals() as $meal) {
                $dish = $meal->getDish();
                if ($dish instanceof DishVariation) {
                    $firstDishVariation = $dish;
                    break 2;
                }
            }
        }

        $this->assertNotNull($firstDishVariation);

        $variationParentDish = $firstDishVariation->getParent();
        $this->assertNotNull($variationParentDish);

        $crawler = $this->getCurrentWeekParticipations()
            ->filter('.meal-title > span > b')
            ->eq(0)
            ->closest('th');

        $template = '<span><b>%s</b><br>%s</span>';
        $html = sprintf($template, $variationParentDish->getTitle(), $firstDishVariation->getTitle());
        // preg_replace() deletes every whitespace after the first
        $this->assertEquals($html, preg_replace('~\\s{2,}~', '', trim($crawler->html())));
    }

    /**
     * Check that the table data prototype is present in the dom
     * @test
     */
    public function checkTableDataPrototype(): void
    {
        $crawler = $this->getCurrentWeekParticipations()->filter('.table-content');
        $tableRow = '<tr class="table-row"><td class="text table-data wide-cell">__name__<\/td>';
        $tableData = '<td class="meal-participation table-data" data-attribute-action=".*__username__"><i class="glyphicon"><\/i><\/td>';

        $regex = '/(' . $tableRow . ')(' . $tableData . ')+(<\/tr>)/';

        $this->assertMatchesRegularExpression($regex, preg_replace("~\\s{2,}~", "", trim($crawler->attr("data-prototype"))));
    }

    /**
     * Check that the profiles list contains the non participating user
     * @test
     */
    public function checkProfileList(): void
    {
        $crawler = $this->getCurrentWeekParticipations()->filter('.profile-list');
        $userName = self::$userLastName . ', ' . self::$userFirstName;
        $this->assertStringContainsString($userName, $crawler->attr("data-attribute-profiles"));
    }

    /**
     * return crawler for the current week participations
     */
    protected function getCurrentWeekParticipations(): Crawler
    {
        $currentWeek = $this->getCurrentWeek();
        $crawler = $this->client->request('GET', '/participations/' . $currentWeek->getId() . '/edit');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        return $crawler;
    }

    /**
     * return current week object
     */
    protected function getCurrentWeek(): Week
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
