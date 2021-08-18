<?php

namespace App\Mealz\MealBundle\Tests\Controller;

use DateTime;
use Doctrine\Common\Collections\Criteria;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Entity\GuestInvitation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealRepository;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\MealBundle\Service\Doorman;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;

/**
 * Meal controller test.
 *
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class MealControllerTest extends AbstractControllerTestCase
{
    /**
     * Prepares test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();

        $this->loadFixtures([
            new LoadWeeks(),
            new LoadDays(),
            new LoadCategories(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadMeals(),
            new LoadRoles(),
            // self::$container is a special container that allow access to private services
            // see: https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    /**
     * Tests the acceptOffer action (accepting a meal offer) in the meal controller.
     * First case: An user accepts an available offer.
     * @test
     */
    public function acceptAvailableOffer()
    {
        $userProfile = $this->getUserProfile();
        $this->loginAsDefaultClient($userProfile);

        //create a test profile
        $profile = $this->createProfile('Max', 'Mustermann' . time());
        $this->persistAndFlushAll([$profile]);

        //get first locked meal and make it an available offer
        $lockedMealsArray = $this->getDoctrine()->getRepository('MealzMealBundle:Meal')->getLockedMeals();
        $firstLockedMeal = $lockedMealsArray[0];
        $participant = $this->createParticipant($profile, $firstLockedMeal);
        $participant->setOfferedAt(time());
        $this->persistAndFlushAll([$participant]);

        //variables for first case
        $date = date_format($firstLockedMeal->getDateTime(), 'Y-m-d');
        $dish = $firstLockedMeal->getDish()->getSlug();

        //first case: accept available offer
        $this->client->request('GET', '/menu/' . $date . '/' . $dish . '/accept-offer');
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'accepting offer failed');
    }

    /**
     * Second case: There are two offers and the user accepts one and automatically takes the one, that was offered earlier.
     * @test
     */
    public function acceptFirstOffer()
    {
        $userProfile = $this->getUserProfile();
        $this->loginAsDefaultClient($userProfile);

        //create a test profile
        $profile = $this->createProfile('Max', 'Mustermann' . time());

        //create second test profile
        $secondProfile = $this->createProfile('Meike', 'Musterfrau' . time());
        $this->persistAndFlushAll([$profile, $secondProfile]);

        //get first locked meal and make it an available offer
        $lockedMealsArray = $this->getDoctrine()->getRepository('MealzMealBundle:Meal')->getLockedMeals();
        $lockedMeal = $lockedMealsArray[0];
        $participant = $this->createParticipant($profile, $lockedMeal);
        $participant->setOfferedAt(time());

        //create second participant for same locked meal and make it an available offer (which was offered after the first one)
        $secondParticipant = $this->createParticipant($secondProfile, $lockedMeal);
        $secondParticipant->setOfferedAt(time() + 1);

        $this->persistAndFlushAll([$participant, $secondParticipant]);

        //variables for first case
        $date = date_format($lockedMeal->getDateTime(), 'Y-m-d');
        $dish = $lockedMeal->getDish()->getSlug();

        //first case: accept available offer
        $this->client->request('GET', '/menu/' . $date . '/' . $dish . '/accept-offer');
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'accepting offer failed');

        //verification by checking the database
        $newParticipant = $this->getDoctrine()->getRepository('MealzMealBundle:Participant')->find($participant->getId());
        $this->assertTrue($newParticipant->getOfferedAt() === 0);

        //second case: check if second offer is still available
        $secondOffer = $this->getDoctrine()->getRepository('MealzMealBundle:Participant')->find($secondParticipant->getId());
        $this->assertTrue($secondOffer->getOfferedAt() != 0, 'second offer was taken');
    }

    /**
     * Third case: An user tries to accept an outdated offer.
     * @test
     */
    public function acceptOutdatedOffer()
    {
        $userProfile = $this->getUserProfile();
        $this->loginAsDefaultClient($userProfile);

        //create a test profile
        $profile = $this->createProfile('Max', 'Mustermann' . time());
        $this->persistAndFlushAll([$profile]);

        //variables for third case
        $outdatedMealsArray = $this->getDoctrine()->getRepository('MealzMealBundle:Meal')->getOutdatedMeals();
        $outdatedMeal = $outdatedMealsArray[0];

        $date = date_format($outdatedMeal->getDateTime(), 'Y-m-d');
        $dish = $outdatedMeal->getDish();

        //third case: accepting outdated offer
        $this->client->request('GET', '/menu/' . $date . '/' . $dish . '/accept-offer');
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertGreaterThanOrEqual(403, $statusCode, 'user accepted outdated offer');
        $this->assertLessThanOrEqual(404, $statusCode, 'user accepted outdated offer');
    }

    /**
     * Testing joining Meal with variations.
     * We have next situation: (1 Dish without variations and 1 Dish with 2 variations)
     * If we can subscribe to all 3 of these options then you can select Dish with and without variations
     *
     * /menu/{date}/{dish}/join/{profile}
     *
     * @test
     *
     */
    public function joinAMealWithVariations()
    {
        // data provider method
        $dataProvider = $this->getJoinAMealData();
        $userProfile = $this->getUserProfile();
        $username = $this->getUserProfile()->getUsername();

        // load a home page
        $this->client->request('GET', '/');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // go through provided data and test functionality
        foreach ($dataProvider as $dataRow) {
            // Call controller actionxxxx
            $slug = $dataRow[1]->getDish()->getSlug();
            $this->client->request('GET', "/menu/$dataRow[0]/$slug/join/$username");

            // Verify if enrollment is successful
            $mealParticipants = $this->getMealParticipants($dataRow[1]);

            /** @var Participant $participant */
            foreach ($mealParticipants as $participant) {
                $profile = $participant->getProfile();

                if ($userProfile->getFirstName() === $profile->getFirstName()
                    && ($userProfile->getName() === $profile->getName())
                    && ($userProfile->getUsername() === $profile->getUsername())
                ) {
                    $this->assertTrue(true);

                    break;
                }
                $this->assertTrue(false);
            }
        }
    }

    /**
     * Searching a Day with 3 options. I adapted fixtures so we always have 1 day with 3 options
     * (1 Dish without variations and 1 Dish with 2 variations)
     *
     * @return array
     */
    public function getJoinAMealData()
    {
        /** @var MealRepository $mealRepository */
        $mealRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
        $meals = $mealRepository->getMealsOnADayWithVariationOptions();

        $mealsArr = array();
        $dataProvider = array();
        foreach ($meals as $meal) {
            /** @var Meal $meal */
            $mealsArr[] = $meal = $mealRepository->find($meal['id']);
            $dataProvider[] = array(date('Y-m-d', $meal->getDay()->getDateTime()->getTimestamp()), $meal);
        }

        // in format [Date, Meal]
        return $dataProvider;
    }

    /**
     * @test
     * @dataProvider getGuestEnrollmentData
     *
     * @param bool $enrollmentStatus Flag whether enrollment should be successful or not.
     */
    public function enrollAsGuest($firstName, $lastName, $company, $selectDish, $enrollmentStatus)
    {
        $userProfile = $this->getUserProfile();
        $meal = $this->getAvailableMeal();

        // Create guest invitation link
        $guestInvitation = new GuestInvitation($userProfile, $meal->getDay());
        $this->persistAndFlushAll([$guestInvitation]);

        // Enroll as guest
        $guestEnrollmentUrl = '/menu/guest/' . $guestInvitation->getId();
        $crawler = $this->client->request('GET', $guestEnrollmentUrl);
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $form = $crawler->filterXPath('//form[@name="invitation_form"]')->form(
            [
                'invitation_form[profile][name]' => $lastName,
                'invitation_form[profile][firstName]' => $firstName,
                'invitation_form[profile][company]' => $company,
            ]
        );

        if ($selectDish) {
            $form['invitation_form[day][meals]'][0]->tick();
        }

        $this->client->submit($form, []);

        // Verify enrollment is successful
        $mealParticipants = $this->getMealParticipants($meal);

        /** @var Participant $participant */
        foreach ($mealParticipants as $participant) {
            $profile = $participant->getProfile();

            if ($firstName === $profile->getFirstName()
                && ($lastName === $profile->getName())
                && ($company === $profile->getCompany())
                && $profile->isGuest()
            ) {
                $this->assertTrue($enrollmentStatus);

                return;
            } else {
                $this->assertFalse($enrollmentStatus);
            }
        }
    }

    /**
     * @return array
     */
    public function getGuestEnrollmentData()
    {
        $time = time();

        return [
            // [FirstName, LastName, Company, Select Dish, Enrollment Status]
            ['Max01:' . $time, 'Mustermann01' . $time, 'Test Comapany01' . $time, false, false],
            ['', 'Mustermann02' . $time, 'Test Comapany02' . $time, true, false],
            ['Max03:' . $time, '', 'Test Comapany03' . $time, true, false],
            ['Max04:' . $time, 'Mustermann04' . $time, '', true, false],
            ['Max05:' . $time, 'Mustermann05' . $time, 'Test Comapany05' . $time, true, true],
        ];
    }

    /**
     * Gets the next available meal.
     * @return Meal
     */
    private function getAvailableMeal()
    {
        $availableMeal = null;

        /** @var MealRepository $mealRepository */
        $mealRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
        $criteria = Criteria::create();
        $meals = $mealRepository->matching($criteria->where(Criteria::expr()->gte('dateTime', new DateTime())));

        if ($meals->count() > 0) {
            /** @var Doorman $doorman */
            $doorman = self::$container->get('mealz_meal.doorman');
            foreach ($meals as $meal) {
                if ($doorman->isToggleParticipationAllowed($meal->getDateTime())) {
                    $availableMeal = $meal;
                    break;
                }
            }
        }

        if ($availableMeal === null) {
            $this->fail('No test meal found.');
        }

        return $availableMeal;
    }

    /**
     * Tests if the new FLag is rendered
     * @test
     */
    public function testNewFlagFromMeal()
    {
        $availableMeal = null;

        /** @var MealRepository $mealRepository */
        $mealRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
        $criteria = Criteria::create();

        // get meal newer than today
        $meals = $mealRepository->matching($criteria->where(Criteria::expr()->gte('dateTime', new DateTime())));

        if ($meals->count() > 0) {
            /** @var Doorman $doorman */
            $doorman = self::$container->get('mealz_meal.doorman');
            foreach ($meals as $meal) {
                if ($doorman->isToggleParticipationAllowed($meal->getDateTime())) {
                    $availableMeal = $meal;
                    break;
                }
            }
        }

        // Test if No new span is in meals
        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $flag = $crawler->filterXPath('//span[@class="new-flag"]')->getNode(0);

        if ($flag === null) {
            $this->fail('Flag not found');
        }
    }

    /**
     * Gets all the participants for a meal.
     *
     * @param  Meal $meal Meal instance
     * @return array
     */
    private function getMealParticipants($meal)
    {
        /** @var ParticipantRepository $participantRepo */
        $participantRepo = $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
        $participants = $participantRepo->findBy(['meal' => $meal->getId()]);

        return $participants;
    }
}
