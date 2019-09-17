<?php

/*
 * This file was part of the HWIOAuthBundle package and was edited for meals
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mealz\UserBundle\Provider;

use \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Mealz\UserBundle\User\OAuthUser;
use Mealz\UserBundle\Entity\Profile;
use \Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * OAuthUserProvider.
 */
class OAuthUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var DoctrineRegistry
     */
    private $doctrineRegistry;

    /**
    *
    * give User the ROLE_OAUTH_USER Role to access meals
    * Use in Combination of /app/config/commons/all/security.yml
     *
     * @var        string
     */
    private $publicRole = 'ROLE_USER';

    /**
     * Map Keycloak Roles to Meals ones
     * @var        array
     */
    private $roleMapping = [
        'meals.admin'   => 'ROLE_KITCHEN_STAFF'
    ];

    /**
     * OAuthUserProvider constructor.
     *
     * @param      Registry  $doctrineRegistry
     */
    public function __construct(Registry $doctrineRegistry)
    {
        $this->doctrineRegistry = $doctrineRegistry;
    }

    /**
     * {@inheritdoc}
     *
     * @param      <type>     $username  The username
     *
     * @return     OAuthUser  ( description_of_the_return_value )
     */
    public function loadUserByUsername($username)
    {
        return new OAuthUser($username);
    }


    /**
     * Loads an user by identifier or create it.
     *
     * @param      Object             $userInformation  The user information
     *
     * @return     OAuthUser|boolean  ( description_of_the_return_value )
     */
    public function loadUserByIdOrCreate($userInformation)
    {
        /**
         * First Check if array Informations are given
         */
        if (gettype($userInformation) === 'object' &&
            property_exists($userInformation, 'preferred_username') === true &&
            property_exists($userInformation, 'family_name') === true &&
            property_exists($userInformation, 'given_name') === true
        ) {
            $profile = $this->doctrineRegistry->getManager()->find(
                'MealzUserBundle:Profile',
                $userInformation->preferred_username
            );
        } else {
            return false;
        }

        /**
         * if Userprofile is null, create User
         */
        if ($profile === null) {
            $profile = $this->createProfile(
                $userInformation->preferred_username,
                $userInformation->family_name,
                str_replace(' ', '', explode(',', $userInformation->given_name)[1])
            );
        }

        $user = new OAuthUser($userInformation->preferred_username);
        $user->setProfile($profile);

        /**
         * give every LDAP User the ROLE_OAUTH_USER Role
         */
        $user->addRole($this->publicRole);

        /**
         * Map Keycloak Roles to Meals Roles
         */
        foreach ($this->roleMapping as $keycloakRole => $mealsRole) {
            /**
             * if the Keycloak User has Roles with mapped Roles in meals. Map it.
             */
            if (array_search($keycloakRole, $userInformation->realm_access->roles) !== false) {
                //$user->addRole($mealsRole);
            }
        }

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
        /**
         * get OAuth User Token Informations
         */
        $accessTokens = explode('.', $response->getAccessToken());
        $userInformation = json_decode(base64_decode($accessTokens[1]));

        return $this->loadUserByIdOrCreate($userInformation);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Mealz\\UserBundle\\User\\OAuthUser';
    }

    /**
     * Creates a profile.
     *
     * @param      \Symfony\Component\Security\Core\User\UserInterface  $username
     * @param      String                                               $givenName  The given name
     * @param      String                                               $surName    The sur name
     *
     * @return     Profile
     */
    protected function createProfile($username, $givenName, $surName)
    {
        $profile = new Profile();
        $profile->setUsername($username);

        $profile->setName($surName);
        $profile->setFirstName($givenName);

        $this->doctrineRegistry->getManager()->persist($profile);
        $this->doctrineRegistry->getManager()->flush();

        return $profile;
    }
}
