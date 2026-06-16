<?php

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\Entity\Role;
use Override;

/**
 * Print controller test.
 */
final class PrintControllerTest extends AbstractControllerTestCase
{
    #[Override]
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
            new LoadUsers(self::getContainer()->get('security.user_password_hasher')),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    /**
     * Check that guest participants are not listed in the cost sheet.
     */
    public function testGuestDoesNotAppearInCostListing(): void
    {
        $this->markTestSkipped('frontend test');
        $time = time();

        // Create guest profile
        $guestFirstName = 'Jon:' . $time;
        $guestLastName = 'Doe:' . $time;
        $guestCompany = 'Test Company:' . $time;

        $guest = $this->createProfile($guestFirstName, $guestLastName, $guestCompany);
        $guest->addRole($this->getRole(Role::ROLE_GUEST));

        // Create profile for normal user
        $userFirstName = 'Max:' . $time;
        $userLastName = 'Mustermann:' . $time;
        $user = $this->createProfile($userFirstName, $userLastName);

        $this->persistAndFlushAll([$guest, $user]);

        // Enroll users to a meal
        $meal = $this->getRecentMeal();
        $this->createParticipant($guest, $meal);
        $this->createParticipant($user, $meal);

        $crawler = $this->client->request('GET', '/print/costsheet');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Ensure that guest does not appear in the listing
        $this->assertEquals(0, $crawler->filter('html:contains("' . $guestFirstName . '")')->count());
        $this->assertEquals(0, $crawler->filter('html:contains("' . $guestLastName . '")')->count());
        $this->assertEquals(0, $crawler->filter('html:contains("' . $guestCompany . '")')->count());

        // Ensure that rendering is correct and non guest users do appear in the listing
        $this->assertEquals(1, $crawler->filter('html:contains("' . $userFirstName . '")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("' . $userLastName . '")')->count());
    }
}
