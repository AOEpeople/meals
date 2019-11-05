<?php

/*
 * This file was part of the HWIOAuthBundle package and was edited for meals
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mealz\UserBundle\Provider;

use \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use \HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as HWIBundleOAuthUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Mealz\UserBundle\User\OAuthUser;
use Mealz\UserBundle\Entity\Profile;
use \Doctrine\Bundle\DoctrineBundle\Registry;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * OAuthUserProvider.
 */
class OAuthUserProvider extends HWIBundleOAuthUserProvider
{
    /**
     * @var DoctrineRegistry
     */
    private $doctrineRegistry;

    /**
     * Map Keycloak Roles to Meals ones
     *
     * @var array
     */
    private $roleMapping = [
        'meals.admin'   => 'ROLE_KITCHEN_STAFF',
        'meals.finance' => 'ROLE_FINANCE',
        'meals.user'    => 'ROLE_USER'
    ];

    /**
     * OAuthUserProvider constructor.
     *
     * @param Registry  $doctrineRegistry
     */
    public function __construct(Registry $doctrineRegistry)
    {
        $this->doctrineRegistry = $doctrineRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->doctrineRegistry->getManager()->find(
            'MealzUserBundle:Profile',
            $username
        );
    }

    /**
     * Loads an user by identifier or create it.
     *
     * @param string $username
     * @param array $userInformation The user information
     *
     * @return OAuthUser|boolean
     */
    public function loadUserByIdOrCreate($username, $userInformation)
    {
        if (array_key_exists('error', $userInformation) && $userInformation['error'] === 'invalid_token') {
            return false;
        }

        $userRoles = $this->fetchUserRoles($userInformation['roles']);

        // When non of the roles fetched - access is denied
        if ($userRoles->isEmpty() === true) {
            return false;
        }
        
        // Check if all informations are given
        if (empty($username) === true ||
            gettype($userInformation) !== 'array' ||
            array_key_exists('family_name', $userInformation) === false ||
            array_key_exists('given_name', $userInformation) === false
        ) {
            return false;
        }

        $user = $this->loadUserByUsername($username);

        // When Userprofile is null, create User
        if ($user === null) {
            $user = $this->createProfile(
                $username,
                $userInformation['given_name'],
                $userInformation['family_name']
            );
        }

        $user->setRoles($userRoles);

        if ($user instanceof Login) {
            $this->doctrineRegistry->getManager()->persist($user);
            $this->doctrineRegistry->getManager()->flush();
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        return $this->loadUserByIdOrCreate($response->getNickname(), $response->getResponse());
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
    public function supportsClass($class)
    {
        return $class === 'Mealz\\UserBundle\\Entity\\Profile';
    }

    /**
     * Creates a profile.
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface  $username
     * @param String $givenName  The given name
     * @param String $surName    The sur name
     *
     * @return     Profile
     */
    protected function createProfile($username, $givenName, $surName)
    {
        $profile = new Profile();
        $profile->setUsername($username);

        $profile->setFirstName($givenName);
        $profile->setName($surName);

        $this->doctrineRegistry->getManager()->persist($profile);
        $this->doctrineRegistry->getManager()->flush();

        return $profile;
    }

    /**
     * Fetch keyCloak and meals roles
     * @param array $keycloakUserRoles
     * 
     * @return array
     */
    protected function fetchUserRoles($keycloakUserRoles) 
    {
        $fetchedRoles = new ArrayCollection();

        // Map Keycloak Roles to Meals Roles
        foreach ($this->roleMapping as $keycloakRoleName => $mealsRole) {
            // if the Keycloak User has Roles with mapped Roles in meals. Map it.
            if (array_search($keycloakRoleName, $keycloakUserRoles) !== false) {
                $fetchedRoles->add($mealsRole);
            }
        }

        return $fetchedRoles;
    }
}
