<?php

namespace App\Mealz\UserBundle\Provider;

use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use App\Mealz\UserBundle\Repository\RoleRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    private const ROLE_ADMIN = 'ROLE_ADMIN';
    private const ROLE_KITCHEN_STAFF = 'ROLE_KITCHEN_STAFF';
    private const ROLE_FINANCE = 'ROLE_FINANCE';
    private const ROLE_USER = 'ROLE_USER';

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

    private EntityManagerInterface $entityManager;
    private RoleRepositoryInterface $roleRepo;

    public function __construct(EntityManagerInterface $entityManager, RoleRepositoryInterface $roleRepo)
    {
        $this->entityManager = $entityManager;
        $this->roleRepo = $roleRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->entityManager->find(Profile::class, $username);
        if ($user instanceof UserInterface) {
            return $user;
        }

        $exception = new UsernameNotFoundException($username . ': user not found', 1629778235);
        $exception->setUsername($username);
        throw $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getNickname();
        $firstName = $response->getFirstName();
        $lastName = $response->getLastName();

        $idpUserRoles = $response->getData()['roles'] ?? [];
        $role = $this->toMealsRole($idpUserRoles);
        $roles = (null === $role) ? [] : [$role];

        try {
            $user = $this->loadUserByUsername($username);
        } catch (UsernameNotFoundException $exception) {
            return $this->createProfile($username, $firstName, $lastName, $roles);
        }

        return $this->updateProfile($user, $firstName, $lastName, $roles);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (false === $this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class): bool
    {
        return Profile::class === $class || is_subclass_of($class, Profile::class);
    }

    /**
     * @param Role[] $roles
     */
    private function createProfile(
        string $username,
        string $firstName,
        string $lastName,
        array $roles
    ): Profile {
        $profile = new Profile();
        $profile->setUsername($username);

        return $this->updateProfile($profile, $firstName, $lastName, $roles);
    }

    /**
     * @param Role[] $roles
     */
    private function updateProfile(
        Profile $profile,
        string $firstName,
        string $lastName,
        array $roles
    ): Profile {
        $profile->setFirstName($firstName);
        $profile->setName($lastName);
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
