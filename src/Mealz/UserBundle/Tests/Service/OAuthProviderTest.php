<?php

namespace App\Mealz\UserBundle\Tests\Service;

use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\Provider\OAuthUserProvider;
use App\Mealz\UserBundle\Entity\Profile;

class OAuthProviderTest extends AbstractControllerTestCase
{
    /**
     * Set up the testing environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createDefaultClient();
        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic'))
        ]);
    }

    /**
     * Test oAuth Provider - create new User and check if admin
     * @test
     */
    public function testCreateNewAdminOAuthUser()
    {
        $oAuthProvider = new OAuthUserProvider($this->getDoctrine());

        $username = 'test.user';
        $newUserInformation = [
            'preferred_username' => $username,
            'family_name' => 'user',
            'given_name' => 'test',
            'roles' => ['meals.admin']
        ];

        // check if valid oAuth User comes in return
        $response = $oAuthProvider->loadUserByIdOrCreate($username, $newUserInformation);
        $this->assertInstanceOf(Profile::class, $response);

        // check if new valid Profile is written in Database
        $newCreatedProfile = $this->getDoctrine()->getManager()->find(
            'MealzUserBundle:Profile',
            $username
        );
        $this->assertEquals('test.user', $newCreatedProfile->getUsername());

        // check if Rolemapping was correct
        $this->assertEquals([0 => 'ROLE_KITCHEN_STAFF'], $response->getRoles());
    }

    /**
     * Test oAuth Provider - get old User and check if roles are mapped corrrectly with false role
     * @test
     */
    public function testValidNewOAuthUser()
    {
        $oAuthProvider = new OAuthUserProvider($this->getDoctrine());

        $username = 'alice';
        $validUserInformation = [
            'preferred_username' => 'alice',
            'family_name' => 'ecila',
            'given_name' => 'alice',
            'roles' => ['meals.user']
        ];

        // check if Response is a valid oAuth User
        $response = $oAuthProvider->loadUserByIdOrCreate($username, $validUserInformation);
        $this->assertInstanceOf(Profile::class, $response);

        // check if new valid Profile is written in Database
        $newCreatedProfile = $this->getDoctrine()->getManager()->find(
            'MealzUserBundle:Profile',
            $username
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

        $username = 'user.test';
        $newUserInformation = [
            'preferred_username' => $username,
            'family_name' => 'test',
            'given_name' => 'user',
            'roles' => [
                'meals.user',
                'meals.finance'
            ]
        ];

        // check if valid oAuth User comes in return
        $response = $oAuthProvider->loadUserByIdOrCreate($username, $newUserInformation);
        $this->assertInstanceOf(Profile::class, $response);

        // check if new valid Profile is written in Database
        $newCreatedProfile = $this->getDoctrine()->getManager()->find(
            'MealzUserBundle:Profile',
            $username
        );
        $this->assertEquals('user.test', $newCreatedProfile->getUsername());

        // check if Rolemapping was correct
        $this->assertEquals([0 => 'ROLE_FINANCE', 1 => 'ROLE_USER'], $response->getRoles());
    }

    /**
     * Test oAuth Provider - detect user with false role
     * @test
     */
    public function testOAuthUserWithInvalidRoles()
    {
        $oAuthProvider = new OAuthUserProvider($this->getDoctrine());

        $username = 'alice';
        $validUserInformation = [
            'preferred_username' => 'alice',
            'family_name' => 'ecila',
            'given_name' => 'alice',
            'roles' => ['system.user']
        ];

        // check if Response is a valid oAuth User
        $response = $oAuthProvider->loadUserByIdOrCreate($username, $validUserInformation);
        $this->assertEquals(false, $response);
    }

    /**
     * Test oAuth Provider - detect invalid token
     * @test
     */
    public function testOAuthUserWithInvalidToken()
    {
        $oAuthProvider = new OAuthUserProvider($this->getDoctrine());

        $username = 'alice';
        $validUserInformation = [
            'error' => 'invalid_token'
        ];

        // check if Response is a valid oAuth User
        $response = $oAuthProvider->loadUserByIdOrCreate($username, $validUserInformation);
        $this->assertEquals(false, $response);
    }
}
