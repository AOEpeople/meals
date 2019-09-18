<?php

namespace Mealz\UserBundle\Tests\Service;

use Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use Mealz\UserBundle\Provider\OAuthUserProvider;
use Mealz\UserBundle\User\OAuthUser;
use Mealz\UserBundle\Entity\Profile;

class OAuthProiderTest extends AbstractControllerTestCase
{
    /**
     * Set up the testing environment
     */
    public function setUp()
    {
        parent::setUp();

        $this->createDefaultClient();
        $this->clearAllTables();
        $this->loadFixtures(
            array(
                new LoadRoles(),
                new LoadUsers($this->client->getContainer())
            )
        );
    }

    /**
     * Test oAuth Provider - create new User and check if admin
     * @test
     */
    public function testCreateNewAdminOAuthUser()
    {
        $oAuthProvider = new OAuthUserProvider($this->getDoctrine());

        $newUserInformation = (object) [
            'preferred_username' => 'test.user',
            'family_name' => 'user',
            'given_name' => 'test',
            'realm_access' => (object)['roles' =>['meals.admin']]
        ];

        // check if valid oAuth User comes in return
        $response = $oAuthProvider->loadUserByIdOrCreate($newUserInformation);
        $this->assertInstanceOf(OAuthUser::class, $response);

        // check if new valid Profile is written in Database
        $newCreatedProfile = $this->getDoctrine()->getManager()->find(
            'MealzUserBundle:Profile',
            $newUserInformation->preferred_username
        );
        $this->assertEquals('test.user', $newCreatedProfile->getUsername());

        // check if Rolemapping was correct
        $this->assertEquals([0 => 'ROLE_USER', 1 => 'ROLE_KITCHEN_STAFF'], $response->getRoles());
    }

    /**
     * Test oAuth Provider - get old User and check if roles are mapped corrrectly with false role
     * @test
     */
    public function testValidNewOAuthUser()
    {
        $oAuthProvider = new OAuthUserProvider($this->getDoctrine());

        $validUserInformation = (object) [
            'preferred_username' => 'alice',
            'family_name' => 'ecila',
            'given_name' => 'alice',
            'realm_access' => (object)['roles' =>['test_role']]
        ];

        // check if Response is a valid oAuth User
        $response = $oAuthProvider->loadUserByIdOrCreate($validUserInformation);
        $this->assertInstanceOf(OAuthUser::class, $response);

        // check if new valid Profile is written in Database
        $newCreatedProfile = $this->getDoctrine()->getManager()->find(
            'MealzUserBundle:Profile',
            $validUserInformation->preferred_username
        );
        $this->assertEquals($newCreatedProfile->getUsername(), 'alice');

        // check if Rolemapping was correct
        $this->assertEquals([0 => 'ROLE_USER'], $response->getRoles());
    }

    /**
     * Test oAuth Provider - create new User and check if finance role is mapped correctly
     * @test
     */
    public function testCreateNewFinanceOAuthUser()
    {
        $oAuthProvider = new OAuthUserProvider($this->getDoctrine());

        $newUserInformation = (object) [
            'preferred_username' => 'user.test',
            'family_name' => 'test',
            'given_name' => 'user',
            'realm_access' => (object)['roles' =>['meals.finance']]
        ];

        // check if valid oAuth User comes in return
        $response = $oAuthProvider->loadUserByIdOrCreate($newUserInformation);
        $this->assertInstanceOf(OAuthUser::class, $response);

        // check if new valid Profile is written in Database
        $newCreatedProfile = $this->getDoctrine()->getManager()->find(
            'MealzUserBundle:Profile',
            $newUserInformation->preferred_username
        );
        $this->assertEquals('user.test', $newCreatedProfile->getUsername());

        // check if Rolemapping was correct
        $this->assertEquals([0 => 'ROLE_USER', 1 => 'ROLE_FINANCE'], $response->getRoles());
    }
}
