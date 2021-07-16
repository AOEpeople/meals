<?php

namespace Mealz\MealBundle\Tests\Controller;

use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
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
    protected function setUp(): void
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
     * check that guest participants are not listed in the costsheet
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
}
