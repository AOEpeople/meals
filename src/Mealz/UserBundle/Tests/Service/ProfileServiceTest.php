<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Tests\Service;

use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;
use App\Mealz\UserBundle\Repository\RoleRepositoryInterface;
use App\Mealz\UserBundle\Service\ProfileService;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Prophecy\PhpUnit\ProphecyTrait;

class ProfileServiceTest extends AbstractDatabaseTestCase
{
    use ProphecyTrait;
    private EntityManagerInterface $em;
    private ProfileRepositoryInterface $profileRepository;
    private RoleRepositoryInterface $roleRepository;
    private ProfileService $profileService;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([new LoadRoles(), new LoadUsers(self::getContainer()->get('security.user_password_hasher'))]);

        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->profileRepository = static::getContainer()->get(ProfileRepositoryInterface::class);
        $this->roleRepository = static::getContainer()->get(RoleRepositoryInterface::class);
        $this->profileService = new ProfileService(
            $this->em,
            $this->profileRepository,
            $this->roleRepository
        );
    }

    public function testCreateNewGuest(): void
    {
        $firstName = 'John';
        $lastName = 'Doe';
        $company = 'Dooey Corp';

        $profile = $this->profileService->createGuest($firstName, $lastName, $company);

        $foundProfile = $this->profileRepository->findOneBy([
            'firstName' => $firstName,
            'name' => $lastName,
            'company' => $company,
        ]);

        $this->assertNotNull($foundProfile);
        $this->assertInstanceOf(Profile::class, $foundProfile);
        $this->assertInstanceOf(Profile::class, $profile['profile']);
        $this->assertEquals($profile['profile'], $foundProfile);
        $this->assertTrue($profile['profile']->isGuest());
    }

    public function testExistingProfile(): void
    {
        $firstName = 'John';
        $lastName = 'Doe';
        $company = 'Dooey Corp';
        $role = $this->roleRepository->findOneBy(['sid' => Role::ROLE_GUEST]);

        $this->assertNotNull($role);

        $profile = new Profile();
        $profile->setFirstName($firstName);
        $profile->setName($lastName);
        $profile->setCompany($company);
        $profile->addRole($role);

        $this->em->persist($profile);
        $this->em->flush();

        $foundProfile = $this->profileRepository->findOneBy([
            'firstName' => $firstName,
            'name' => $lastName,
            'company' => $company,
        ]);

        $this->assertNotNull($foundProfile);

        $guest = $this->profileService->createGuest($firstName, $lastName, $company);
        $this->assertInstanceOf(Profile::class, $guest['profile']);
        $this->assertEquals($guest['profile'], $foundProfile);
        $this->assertTrue($guest['profile']->isGuest());
    }
}
