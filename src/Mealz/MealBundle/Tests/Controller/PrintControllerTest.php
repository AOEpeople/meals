<?php

namespace Mealz\MealBundle\Tests\Controller;

use Doctrine\Common\Collections\Criteria;
use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Mealz\UserBundle\Entity\Profile;
use Mealz\UserBundle\Entity\Role;

/**
 * Print controller test.
 *
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class PrintControllerTest extends AbstractControllerTestCase
{
    /**
     * Prepares test environment.
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
            new LoadMeals(),
            new LoadRoles(),
            new LoadUsers($this->client->getContainer()),
        ]);
    }

    /**
     * @test
     */
    public function guestDoesNotAppearInCostListing()
    {
        $time = time();

        // Create guest profile
        $guestFirstName = 'Jon:'.$time;
        $guestLastName  = 'Doe:'.$time;
        $guestCompany   = 'Test Company:'.$time;

        $guest = $this->createProfile($guestFirstName, $guestLastName, $guestCompany);
        $guest->addRole($this->getRole(Role::ROLE_GUEST));

        // Create profile for normal user
        $userFirstName = 'Max:'.$time;
        $userLastName  = 'Mustermann:'.$time;
        $user = $this->createProfile($userFirstName, $userLastName);

        $this->persistAndFlushAll([$guest, $user]);

        // Enroll users to a meal
        $meal = $this->getRecentMeal();
        $this->createParticipant($guest, $meal);
        $this->createParticipant($user, $meal);

        $crawler = $this->client->request('GET', '/print/costsheet');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Ensure that guest does not appear in the listing
        $this->assertEquals(0, $crawler->filter('html:contains("'.$guestFirstName.'")')->count());
        $this->assertEquals(0, $crawler->filter('html:contains("'.$guestLastName.'")')->count());
        $this->assertEquals(0, $crawler->filter('html:contains("'.$guestCompany.'")')->count());

        // Ensure that rendering is correct and non guest users do appear in the listing
        $this->assertEquals(1, $crawler->filter('html:contains("'.$userFirstName.'")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("'.$userLastName.'")')->count());
    }

    /**
     * Gets a user role.
     *
     * @param  string $roleType     Role string identifier i.e. sid.
     *
     * @return Role
     */
    protected function getRole($roleType)
    {
        /** @var \Mealz\UserBundle\Entity\RoleRepository $roleRepository */
        $roleRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Role');
        /** @var \Mealz\UserBundle\Entity\Role $guestRole */
        $role = $roleRepository->findOneBy(['sid' => $roleType]);

        if (!($role instanceof Role)) {
            $this->fail('User role not "'.$roleType.'" found.');
        }


        return $role;
    }

    /**
     * Creates a new user profile object.
     *
     * @param  string $firstName    User first name
     * @param  string $lastName     User last name
     * @param  string $company      User company
     * @return Profile
     */
    protected function createProfile($firstName = '', $lastName = '', $company = '')
    {
        $firstName = $firstName ? $firstName : 'Test';
        $lastName = $lastName ? $lastName : 'User'.rand();
        $company = $company ? $company : rand();

        $profile = new Profile();
        $profile->setUsername($firstName.'.'.$lastName);
        $profile->setFirstName($firstName);
        $profile->setName($lastName);

        if ($company) {
            $profile->setCompany($company);
        }

        return $profile;
    }

    /**
     * Creates a new participant object.
     *
     * @param  Profile $profile     User profile
     * @param  Meal $meal           Meal instance
     *
     * @return Participant
     */
    protected function createParticipant($profile, $meal)
    {
        $participant = new Participant();
        $participant->setProfile($profile);
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
