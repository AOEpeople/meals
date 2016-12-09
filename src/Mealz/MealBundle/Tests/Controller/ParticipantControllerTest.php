<?php

namespace Mealz\MealBundle\Tests\Controller;

use Doctrine\Common\Collections\Criteria;
use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadParticipants;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\Week;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Mealz\UserBundle\Entity\Profile;

/**
 * Participant Controller Test
 *
 * @author Henry Vogt <henry.vogt@aoe.com>
 */
class ParticipantControllerTest extends AbstractControllerTestCase
{
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
    }

    /**
     * @test
     */
    public function checkParticipantInParticipationTable()
    {
        $time = time();

        // Create profile for user
        $userFirstName = 'Max:'.$time;
        $userLastName  = 'Mustermann:'.$time;
        // Enroll users to a meal
        $meal = $this->getRecentMeal();
        $this->createProfileAndParticipation($userFirstName, $userLastName, $meal);
        /** @var WeekRepository $weekRepository */
        $weekRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Week');
        /** @var Week $currentWeek */
        $currentWeek = $weekRepository->getCurrentWeek();

        $crawler = $this->client->request('GET', '/participations/'.$currentWeek->getId().'/edit');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->assertEquals(1, $crawler->filter('html:contains("'.$userFirstName.'")')->count());
    }

    /**
     * Creates a new user profile object.
     *
     * @param  string $firstName    User first name
     * @param  string $lastName     User last name
     * @return Profile
     */
    protected function createProfile($firstName = '', $lastName = '')
    {
        $firstName = $firstName ? $firstName : 'Test';
        $lastName = $lastName ? $lastName : 'User'.rand();

        $profile = new Profile();
        $profile->setUsername($firstName.'.'.$lastName);
        $profile->setFirstName($firstName);
        $profile->setName($lastName);

        return $profile;
    }

    /**
     * create profile for user and add participation
     * @param $userFirstName
     * @param $userLastName
     * @param $meal
     * @return Participant
     */
    protected function createProfileAndParticipation($userFirstName, $userLastName, $meal)
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
     * Gets the recent meal.
     *
     * @return Meal
     */
    private function getRecentMeal()
    {
        /** @var \Mealz\MealBundle\Entity\MealRepository $mealRepository */
        $mealRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
        $criteria = Criteria::create();
        $meals = $mealRepository->matching($criteria->where(Criteria::expr()->lte('dateTime', new \DateTime())));

        if (1 > $meals->count()) {
            $this->fail('No test meal found.');
        }

        return $meals->first();
    }
}
