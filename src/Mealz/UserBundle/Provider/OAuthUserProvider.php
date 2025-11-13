<?php

namespace App\Mealz\UserBundle\Provider;

use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use App\Mealz\UserBundle\Repository\RoleRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<Profile>
 */
final class OAuthUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    private const string ROLE_ADMIN = 'ROLE_ADMIN';
    private const string ROLE_KITCHEN_STAFF = 'ROLE_KITCHEN_STAFF';
    private const string ROLE_FINANCE = 'ROLE_FINANCE';
    private const string ROLE_USER = 'ROLE_USER';

    /**
     * Map Keycloak Roles to Meals ones.
     *
     * @var array<string, string>
     */
    private array $roleMapping = [
        'meals.admin' => self::ROLE_ADMIN,
        'meals.kitchen' => self::ROLE_KITCHEN_STAFF,
        'meals.finance' => self::ROLE_FINANCE,
        'meals.user' => self::ROLE_USER,
        'aoe_employee' => self::ROLE_USER,
    ];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly RoleRepositoryInterface $roleRepo,
        private readonly string $authClientID
    ) {
    }

    #[Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->entityManager->find(Profile::class, $identifier);
        if (!($user instanceof UserInterface)) {
            throw new UserNotFoundException(sprintf('user not found: %s', $identifier));
        }

        return $user;
    }

    /**
     * Loads an user by identifier or create it.
     */
    public function loadUserByIdOrCreate(UserResponseInterface $response): ?UserInterface
    {
        $data = $response->getData();

        $idpUserId = $data['sub'];
        $username = $response->getNickname();
        $firstName = $response->getFirstName() ?? '';
        $lastName = $response->getLastName() ?? '';
        $email = $response->getEmail();

        $globalUserRoles = $data['roles'] ?? [];
        $appUserRoles = $data['resource_access'][$this->authClientID]['roles'] ?? [];
        $role = $this->toMealsRole(array_merge($globalUserRoles, $appUserRoles));
        $roles = (null === $role) ? [] : [$role];

        try {
            $user = $this->loadUserByIdentifier($idpUserId);
        } catch (UserNotFoundException $e) {
            // Find user by username; set IdpUser ID
            $user = $this->entityManager->getRepository(Profile::class)->findOneBy(['username' => $username]);
            if ($user instanceof UserInterface) {
                $user->setId($idpUserId);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $user = $this->entityManager->find(Profile::class, $idpUserId);
            }

            // Create user
            if (false === ($user instanceof UserInterface)) {
                try {
                    return $this->createProfile($idpUserId, $username, $firstName, $lastName, $email, $roles);
                } catch (Exception $e) {
                    $this->logger->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);

                    return null;
                }
            }
        }

        if (!($user instanceof Profile)) {
            throw new Exception('invalid user instance, expected instance of Profile, got' . gettype($user), 1716299772);
        }

        return $this->updateProfile($user, $firstName, $lastName, $email, $roles);
    }

    /**
     * @throws Exception
     */
    #[Override]
    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $user = $this->loadUserByIdOrCreate($response);
        if (null === $user) {
            throw new UserNotFoundException($response->getNickname() . ': not found', 1618307277);
        }

        return $user;
    }

    #[Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (false === $this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)), 1716299773);
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    #[Override]
    public function supportsClass(string $class): bool
    {
        return Profile::class === $class || is_subclass_of($class, Profile::class);
    }

    /**
     * @param Role[] $roles
     */
    private function createProfile(
        string $idpUserId,
        string $username,
        string $firstName,
        string $lastName,
        ?string $email,
        array $roles
    ): Profile {
        $profile = new Profile();
        $profile->setUsername($username);
        $profile->setId($idpUserId);

        return $this->updateProfile($profile, $firstName, $lastName, $email, $roles);
    }

    /**
     * @param Role[] $roles
     */
    private function updateProfile(
        Profile $profile,
        string $firstName,
        string $lastName,
        ?string $email,
        array $roles
    ): Profile {
        $profile->setFirstName($firstName);
        $profile->setName($lastName);
        $profile->setEmail($email);
        $profile->setHidden(false);
        $profile->setRoles(new ArrayCollection($roles));

        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        return $profile;
    }

    private function toMealsRole(array $idpRoles): ?Role
    {
        $mappedRoles = array_intersect_key($this->roleMapping, array_flip($idpRoles));
        if (0 === count($mappedRoles)) {
            return null;
        }

        $mappedRoles = array_unique(array_values($mappedRoles));
        $maxPrivilegedRole = $this->getMaxPrivilegedRole($mappedRoles);
        $roles = $this->roleRepo->findBySID([$maxPrivilegedRole]);

        return (0 < count($roles)) ? array_shift($roles) : null;
    }

    private function getMaxPrivilegedRole(array $roles): string
    {
        if (in_array(self::ROLE_ADMIN, $roles, true)) {
            return self::ROLE_ADMIN;
        }

        if (in_array(self::ROLE_KITCHEN_STAFF, $roles, true)) {
            return self::ROLE_KITCHEN_STAFF;
        }

        if (in_array(self::ROLE_FINANCE, $roles, true)) {
            return self::ROLE_FINANCE;
        }

        return self::ROLE_USER;
    }
}
