<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Tests\Service;

use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Provider\OAuthUserProvider;
use App\Mealz\UserBundle\Repository\RoleRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Override;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class OAuthProviderTest extends AbstractControllerTestCase
{
    use ProphecyTrait;

    private const string AUTH_CLIENT_ID = 'meals-app';

    private OAuthUserProvider $sut;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([new LoadRoles()]);

        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();

        $this->sut = new OAuthUserProvider(
            $em,
            self::getContainer()->get(RoleRepositoryInterface::class),
            self::AUTH_CLIENT_ID
        );
    }

    /**
     * Test oAuth Provider - create new User and check if admin.
     *
     * @dataProvider idpUserDataProvider
     */
    public function testCreateNewUser(array $idpUserData, array $mealsRoles): void
    {
        $firstName = $idpUserData['given_name'];
        $lastName = $idpUserData['family_name'];
        $username = $idpUserData['username'];
        $email = $idpUserData['email'];
        $idpRoles = $idpUserData['roles'];

        $userResponseMock = $this->getMockedUserResponse($username, $firstName, $lastName, $email, $idpRoles);

        // check if valid oAuth User comes in return
        $user = $this->sut->loadUserByOAuthUserResponse($userResponseMock);
        $this->assertInstanceOf(Profile::class, $user);

        // check if new valid Profile is written in Database
        $newCreatedProfile = $this->getDoctrine()->getManager()->find(Profile::class, $username);
        $this->assertNotNull($newCreatedProfile);
        $this->assertEquals($username, $newCreatedProfile->getUsername());

        // check role mapping
        $this->assertSame($mealsRoles, $user->getRoles());

        // check that user is not hidden after loading
        $this->assertFalse($user->isHidden());
    }

    public function idpUserDataProvider(): array
    {
        return [
            'admin' => [
                'idpUserData' => [
                    'username' => 'kochomi.meals',
                    'given_name' => 'kochomi',
                    'family_name' => 'imohcok',
                    'email' => 'kochomi.meals@aoe.com',
                    'roles' => ['meals.admin'],
                ],
                'mealsRoles' => ['ROLE_ADMIN'],
            ],
            'kitchen staff' => [
                'idpUserData' => [
                    'username' => 'kochomi.meals',
                    'given_name' => 'kochomi',
                    'family_name' => 'imohcok',
                    'email' => 'kochomi.meals@aoe.com',
                    'roles' => ['meals.kitchen'],
                ],
                'mealsRoles' => ['ROLE_KITCHEN_STAFF'],
            ],
            'standard user' => [
                'idpUserData' => [
                    'username' => 'alice.meals',
                    'given_name' => 'alice',
                    'family_name' => 'ecila',
                    'email' => 'alice.meals@aoe.com',
                    'roles' => ['meals.user'],
                ],
                'mealsRoles' => ['ROLE_USER'],
            ],
            'finance' => [
                'idpUserData' => [
                    'username' => 'finance.meals',
                    'given_name' => 'finance',
                    'family_name' => 'ecnanif',
                    'email' => 'finance.meals@aoe.com',
                    'roles' => ['meals.user', 'meals.finance'],
                ],
                'mealsRoles' => ['ROLE_FINANCE'],
            ],
            'user with invalid role' => [
                'idpUserData' => [
                    'username' => 'invalid.role',
                    'given_name' => 'invalid',
                    'family_name' => 'role',
                    'email' => 'invalid.role@aoe.com',
                    'roles' => ['invalid.role'],
                ],
                'mealsRoles' => [],
            ],
        ];
    }

    /**
     * Returns the mocked response from identity provider.
     *
     * @psalm-suppress UndefinedMagicMethod
     */
    private function getMockedUserResponse(
        string $username, string $firstName, string $lastName, ?string $email, array $roles
    ): object {
        $userData = [
            'preferred_username' => $username,
            'family_name' => $lastName,
            'given_name' => $firstName,
            'email' => $email,
            'resource_access' => [
                self::AUTH_CLIENT_ID => [
                    'roles' => $roles,
                ],
            ],
        ];
        $responseProphet = $this->prophesize(UserResponseInterface::class);
        $responseProphet->getData()->shouldBeCalledOnce()->willReturn($userData);
        $responseProphet->getFirstName()->shouldBeCalledOnce()->willReturn($firstName);
        $responseProphet->getLastName()->shouldBeCalledOnce()->willReturn($lastName);
        $responseProphet->getNickname()->shouldBeCalledOnce()->willReturn($username);
        $responseProphet->getEmail()->shouldBeCalledOnce()->willReturn($email);

        return $responseProphet->reveal();
    }
}
