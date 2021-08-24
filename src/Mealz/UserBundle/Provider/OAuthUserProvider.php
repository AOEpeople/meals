<?php

namespace App\Mealz\UserBundle\Provider;

use App\Mealz\UserBundle\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{

    /**
     * Map Keycloak Roles to Meals ones
     *
     * @var array<string, string>
     */
    private array $roleMapping = [
        'meals.admin'   => 'ROLE_KITCHEN_STAFF',
        'meals.finance' => 'ROLE_FINANCE',
        'meals.user'    => 'ROLE_USER'
    ];

    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
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

        $exception = new UsernameNotFoundException($username.': user not found', 1629778235);
        $exception->setUsername($username);
        throw $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getNickname();

        try {
            return $this->loadUserByUsername($username);
        } catch (UsernameNotFoundException $exception) {
            $idpUserRoles = $response->getData()['roles'] ?? [];
            $mealsUserRoles = $this->toMealsRoles($idpUserRoles);

            return $this->createProfile(
                $username,
                $response->getFirstName(),
                $response->getLastName(),
                $mealsUserRoles
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if ($this->supportsClass(get_class($user)) === false) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class): bool
    {
        return $class === Profile::class;
    }

    protected function createProfile(
        string $username,
        string $firstName,
        string $lastName,
        array $roles
    ): Profile {
        $profile = new Profile();
        $profile->setUsername($username);
        $profile->setFirstName($firstName);
        $profile->setName($lastName);
        $profile->setRoles(new ArrayCollection($roles));

        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        return $profile;
    }

    /**
     * @return list<Role>
     */
    private function toMealsRoles(array $idpRoles): array
    {
        $roles = [];

        foreach ($idpRoles as $idpRole) {
            if (isset($this->roleMapping[$idpRole])) {
                $roleRepository = $this->entityManager->getRepository(Role::class);
                $role = $roleRepository->findOneBy(['sid' => $this->roleMapping[$idpRole]]);
                if (!($role instanceof Role)) {
                    $this->logger->error('role not found', ['sid' => $this->roleMapping[$idpRole]]);
                }

                $roles[] = $role;
            }
        }

        return $roles;
    }
}
